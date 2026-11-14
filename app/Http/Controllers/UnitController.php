<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        $units = $property->units()->latest()->get();

        return view('units.index', compact('property', 'units'));
    }

    public function create(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        return view('units.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $this->authorizeOwner($request, $property);

        $data = $this->validateData($request);

        $unit = $property->units()->create($data);

        return redirect()->route('units.show', $unit)
            ->with('success', 'Unit created.');
    }

    public function show(Request $request, Unit $unit)
    {
        $this->authorizeOwner($request, $unit->property);

        $unit->load(['property', 'activeLease', 'leases.rentInvoices', 'maintenanceRequests']);

        return view('units.show', compact('unit'));
    }

    public function edit(Request $request, Unit $unit)
    {
        $this->authorizeOwner($request, $unit->property);

        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorizeOwner($request, $unit->property);

        $data = $this->validateData($request);

        $unit->update($data);

        return redirect()->route('units.show', $unit)
            ->with('success', 'Unit updated.');
    }

    public function destroy(Request $request, Unit $unit)
    {
        $this->authorizeOwner($request, $unit->property);

        $unit->delete();

        return redirect()->route('properties.show', $unit->property)
            ->with('success', 'Unit deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'label'       => ['required', 'string', 'max:80'],
            'floor'       => ['nullable', 'string', 'max:30'],
            'bedrooms'    => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'   => ['nullable', 'integer', 'min:0', 'max:20'],
            'square_feet' => ['nullable', 'integer', 'min:0'],
            'market_rent' => ['nullable', 'numeric', 'min:0'],
            'is_occupied' => ['nullable', 'boolean'],
        ]);
    }

    protected function authorizeOwner(Request $request, Property $property): void
    {
        if ($property->user_id !== $request->user()->id) {
            abort(403, 'You do not own this property.');
        }
    }
}
