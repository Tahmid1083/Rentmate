<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = Property::with('units')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $property = $request->user()->properties()->create($data);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property created.');
    }

    public function show(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        $property->load(['units.activeLease', 'units.maintenanceRequests']);

        return view('properties.show', compact('property'));
    }

    public function edit(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        return view('properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        $data = $this->validateData($request);

        $property->update($data);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property updated.');
    }

    public function destroy(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:160'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:120'],
            'state'          => ['nullable', 'string', 'max:120'],
            'postal_code'    => ['nullable', 'string', 'max:30'],
            'country'        => ['nullable', 'string', 'max:60'],
            'type'           => ['nullable', 'string', 'in:residential,commercial,mixed'],
            'notes'          => ['nullable', 'string'],
        ]);
    }

    protected function authorizeOwner(Request $request, Property $property): void
    {
        if ($property->user_id !== $request->user()->id) {
            abort(403, 'You do not own this property.');
        }
    }
}
