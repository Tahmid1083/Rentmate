<?php

namespace App\Http\Controllers;

use App\Models\TenantLease;
use App\Models\Unit;
use Illuminate\Http\Request;

class TenantLeaseController extends Controller
{
    public function index(Request $request)
    {
        $leases = TenantLease::query()
            ->whereHas('unit', function ($q) use ($request) {
                $q->whereHas('property', function ($pq) use ($request) {
                    $pq->where('user_id', $request->user()->id);
                });
            })
            ->with(['unit.property'])
            ->latest()
            ->paginate(15);

        return view('leases.index', compact('leases'));
    }

    public function create(Request $request)
    {
        $units = $this->landlordUnits($request);

        return view('leases.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $unit = Unit::findOrFail($data['unit_id']);
        $this->authorizeUnit($request, $unit);

        $lease = TenantLease::create($data);

        // Mark unit as occupied if this lease is active.
        if ($lease->status === TenantLease::STATUS_ACTIVE) {
            $unit->update(['is_occupied' => true]);
        }

        return redirect()->route('leases.show', $lease)
            ->with('success', 'Lease created.');
    }

    public function show(Request $request, TenantLease $lease)
    {
        $this->authorizeLease($request, $lease);

        $lease->load(['unit.property', 'rentInvoices.payments']);

        return view('leases.show', compact('lease'));
    }

    public function edit(Request $request, TenantLease $lease)
    {
        $this->authorizeLease($request, $lease);

        $units = $this->landlordUnits($request);

        return view('leases.edit', compact('lease', 'units'));
    }

    public function update(Request $request, TenantLease $lease)
    {
        $this->authorizeLease($request, $lease);

        $data = $this->validateData($request);

        $lease->update($data);

        // Sync occupancy on the unit.
        $unit = $lease->unit;
        $unit->update([
            'is_occupied' => $lease->fresh()->status === TenantLease::STATUS_ACTIVE,
        ]);

        return redirect()->route('leases.show', $lease)
            ->with('success', 'Lease updated.');
    }

    public function destroy(Request $request, TenantLease $lease)
    {
        $this->authorizeLease($request, $lease);

        $lease->delete();

        return redirect()->route('leases.index')
            ->with('success', 'Lease deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'unit_id'      => ['required', 'exists:units,id'],
            'tenant_name'  => ['required', 'string', 'max:160'],
            'tenant_email' => ['nullable', 'email', 'max:255'],
            'tenant_phone' => ['nullable', 'string', 'max:40'],
            'lease_start'  => ['required', 'date'],
            'lease_end'    => ['nullable', 'date', 'after_or_equal:lease_start'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit'      => ['nullable', 'numeric', 'min:0'],
            'status'       => ['required', 'in:active,upcoming,expired,terminated'],
            'notes'        => ['nullable', 'string'],
        ]);
    }

    protected function landlordUnits(Request $request)
    {
        return Unit::whereHas('property', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('property')->orderBy('label')->get();
    }

    protected function authorizeUnit(Request $request, Unit $unit): void
    {
        if ($unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizeLease(Request $request, TenantLease $lease): void
    {
        if ($lease->unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
