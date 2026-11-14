@extends('layouts.app')
@section('title', 'Add unit to ' . $property->name)
@section('content')
<div class="card" style="max-width:680px;">
    <form method="post" action="{{ route('properties.units.store', $property) }}">
        @csrf
        <div class="grid cols-2">
            <div class="form-group">
                <label for="label">Unit label *</label>
                <input type="text" name="label" id="label" placeholder="e.g. Apt 12, Suite 200" value="{{ old('label') }}" required>
            </div>
            <div class="form-group">
                <label for="floor">Floor</label>
                <input type="text" name="floor" id="floor" value="{{ old('floor') }}">
            </div>
            <div class="form-group">
                <label for="bedrooms">Bedrooms</label>
                <input type="number" name="bedrooms" id="bedrooms" min="0" max="20" value="{{ old('bedrooms', 0) }}">
            </div>
            <div class="form-group">
                <label for="bathrooms">Bathrooms</label>
                <input type="number" name="bathrooms" id="bathrooms" min="0" max="20" value="{{ old('bathrooms', 0) }}">
            </div>
            <div class="form-group">
                <label for="square_feet">Square feet</label>
                <input type="number" name="square_feet" id="square_feet" min="0" value="{{ old('square_feet') }}">
            </div>
            <div class="form-group">
                <label for="market_rent">Market rent ($)</label>
                <input type="number" name="market_rent" id="market_rent" step="0.01" min="0" value="{{ old('market_rent') }}">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label style="display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="is_occupied" value="1" @checked(old('is_occupied')) style="width:auto;">
                    Mark as occupied
                </label>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Create unit</button>
            <a href="{{ route('properties.show', $property) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
