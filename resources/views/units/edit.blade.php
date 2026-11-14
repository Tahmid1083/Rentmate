@extends('layouts.app')
@section('title', 'Edit unit ' . $unit->label)
@section('content')
<div class="card" style="max-width:680px;">
    <form method="post" action="{{ route('units.update', $unit) }}">
        @csrf @method('PUT')
        <div class="grid cols-2">
            <div class="form-group">
                <label for="label">Unit label *</label>
                <input type="text" name="label" id="label" value="{{ old('label', $unit->label) }}" required>
            </div>
            <div class="form-group">
                <label for="floor">Floor</label>
                <input type="text" name="floor" id="floor" value="{{ old('floor', $unit->floor) }}">
            </div>
            <div class="form-group">
                <label for="bedrooms">Bedrooms</label>
                <input type="number" name="bedrooms" id="bedrooms" min="0" max="20" value="{{ old('bedrooms', $unit->bedrooms) }}">
            </div>
            <div class="form-group">
                <label for="bathrooms">Bathrooms</label>
                <input type="number" name="bathrooms" id="bathrooms" min="0" max="20" value="{{ old('bathrooms', $unit->bathrooms) }}">
            </div>
            <div class="form-group">
                <label for="square_feet">Square feet</label>
                <input type="number" name="square_feet" id="square_feet" min="0" value="{{ old('square_feet', $unit->square_feet) }}">
            </div>
            <div class="form-group">
                <label for="market_rent">Market rent ($)</label>
                <input type="number" name="market_rent" id="market_rent" step="0.01" min="0" value="{{ old('market_rent', $unit->market_rent) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="is_occupied" value="1" @checked(old('is_occupied', $unit->is_occupied)) style="width:auto;">
                    Mark as occupied
                </label>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('units.show', $unit) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
