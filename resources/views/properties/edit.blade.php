@extends('layouts.app')
@section('title', 'Edit property')
@section('content')
<div class="card" style="max-width:760px;">
    <form method="post" action="{{ route('properties.update', $property) }}">
        @csrf @method('PUT')
        <div class="grid cols-2">
            <div class="form-group">
                <label for="name">Property name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $property->name) }}" required>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type">
                    @foreach (['residential' => 'Residential', 'commercial' => 'Commercial', 'mixed' => 'Mixed use'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('type', $property->type) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address_line_1">Address line 1 *</label>
                <input type="text" name="address_line_1" id="address_line_1" value="{{ old('address_line_1', $property->address_line_1) }}" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address_line_2">Address line 2</label>
                <input type="text" name="address_line_2" id="address_line_2" value="{{ old('address_line_2', $property->address_line_2) }}">
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" id="city" value="{{ old('city', $property->city) }}">
            </div>
            <div class="form-group">
                <label for="state">State / Province</label>
                <input type="text" name="state" id="state" value="{{ old('state', $property->state) }}">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal code</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $property->postal_code) }}">
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" name="country" id="country" value="{{ old('country', $property->country) }}">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', is_array($property->notes) ? implode("\n", $property->notes) : (string) $property->notes) }}</textarea>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('properties.show', $property) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
