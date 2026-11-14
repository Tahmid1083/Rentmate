@extends('layouts.app')
@section('title', 'Add property')
@section('content')
<div class="card" style="max-width:760px;">
    <form method="post" action="{{ route('properties.store') }}">
        @csrf
        <div class="grid cols-2">
            <div class="form-group">
                <label for="name">Property name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type">
                    @foreach (['residential' => 'Residential', 'commercial' => 'Commercial', 'mixed' => 'Mixed use'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address_line_1">Address line 1 *</label>
                <input type="text" name="address_line_1" id="address_line_1" value="{{ old('address_line_1') }}" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="address_line_2">Address line 2</label>
                <input type="text" name="address_line_2" id="address_line_2" value="{{ old('address_line_2') }}">
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" id="city" value="{{ old('city') }}">
            </div>
            <div class="form-group">
                <label for="state">State / Province</label>
                <input type="text" name="state" id="state" value="{{ old('state') }}">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal code</label>
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" name="country" id="country" value="{{ old('country', 'US') }}">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Create property</button>
            <a href="{{ route('properties.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
