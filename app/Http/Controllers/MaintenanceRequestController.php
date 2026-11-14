<?php

namespace App\Http\Controllers;

use App\Mail\MaintenanceStatusUpdatedMail;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRequest::query()
            ->whereHas('property', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orWhereHas('unit.property', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with(['unit.property', 'property', 'reporter']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(15)->appends($request->query());

        return view('maintenance.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('user_id', $request->user()->id)
            ->with('units')
            ->orderBy('name')
            ->get();

        return view('maintenance.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Validate ownership of property / unit.
        $property = Property::find($data['property_id']);
        abort_unless($property && $property->user_id === $request->user()->id, 403);

        if (! empty($data['unit_id'])) {
            $unit = Unit::find($data['unit_id']);
            abort_unless($unit && $unit->property_id === $property->id, 403);
        } else {
            $data['unit_id'] = null;
        }

        // Photo upload — stored under the public maintenance disk so the
        // landlord can preview it inline; switch to 'private' if you want
        // downloads to be auth-gated instead.
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')
                ->store('maintenance', 'public');
        }

        $data['reporter_id'] = $request->user()->id;

        $mr = MaintenanceRequest::create($data);
        $mr->pushTimeline('created', 'Maintenance request created.');

        return redirect()->route('maintenance.show', $mr)
            ->with('success', 'Maintenance request created.');
    }

    public function show(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeOwner($request, $maintenanceRequest);

        $maintenanceRequest->load(['unit.property', 'property', 'reporter', 'timeline.user']);

        return view('maintenance.show', compact('maintenanceRequest'));
    }

    public function edit(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeOwner($request, $maintenanceRequest);

        $properties = Property::where('user_id', $request->user()->id)
            ->with('units')
            ->orderBy('name')
            ->get();

        return view('maintenance.edit', compact('maintenanceRequest', 'properties'));
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeOwner($request, $maintenanceRequest);

        $data = $this->validateData($request);

        $property = Property::find($data['property_id']);
        abort_unless($property && $property->user_id === $request->user()->id, 403);

        if (! empty($data['unit_id'])) {
            $unit = Unit::find($data['unit_id']);
            abort_unless($unit && $unit->property_id === $property->id, 403);
        } else {
            $data['unit_id'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($maintenanceRequest->photo_path) {
                Storage::disk('public')->delete($maintenanceRequest->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('maintenance', 'public');
        }

        $maintenanceRequest->update($data);

        return redirect()->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Maintenance request updated.');
    }

    /**
     * Update just the status and push a timeline entry.
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeOwner($request, $maintenanceRequest);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,on_hold,completed,cancelled'],
            'note'   => ['nullable', 'string'],
        ]);

        $old = $maintenanceRequest->status;
        $maintenanceRequest->status = $data['status'];

        if ($data['status'] === MaintenanceRequest::STATUS_COMPLETED) {
            $maintenanceRequest->completed_at = now();
        } else {
            $maintenanceRequest->completed_at = null;
        }

        $maintenanceRequest->save();

        $maintenanceRequest->pushTimeline(
            'status_changed',
            "Status changed from {$old} to {$data['status']}." . ($data['note'] ? ' Note: ' . $data['note'] : '')
        );

        // Email the landlord (if they opted in).
        $user = $maintenanceRequest->property?->user ?? $maintenanceRequest->unit?->property?->user;
        if ($user) {
            $setting = Setting::for($user);
            if ($setting->notify_maintenance_status && $user->email) {
                Mail::to($user->email)->send(new MaintenanceStatusUpdatedMail(
                    $maintenanceRequest, $setting, $old, $data['status'], $data['note'] ?? null
                ));
            }
        }

        return redirect()->route('maintenance.show', $maintenanceRequest)
            ->with('success', 'Status updated.');
    }

    public function destroy(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorizeOwner($request, $maintenanceRequest);

        if ($maintenanceRequest->photo_path) {
            Storage::disk('public')->delete($maintenanceRequest->photo_path);
        }

        $maintenanceRequest->delete();

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance request deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'unit_id'     => ['nullable', 'exists:units,id'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category'    => ['required', 'string', 'in:plumbing,electrical,hvac,appliance,structural,pest_control,other'],
            'priority'    => ['required', 'string', 'in:low,medium,high,urgent'],
            'status'      => ['nullable', 'string', 'in:open,in_progress,on_hold,completed,cancelled'],
            'photo'       => ['nullable', 'image', 'max:5120'], // 5MB
        ]);
    }

    protected function authorizeOwner(Request $request, MaintenanceRequest $mr): void
    {
        $ownerId = $mr->property?->user_id ?? $mr->unit?->property?->user_id;
        if ($ownerId !== $request->user()->id) {
            abort(403);
        }
    }
}
