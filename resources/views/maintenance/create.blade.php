@extends('layouts.app')
@section('title', 'New maintenance request')
@section('content')
<div class="card" style="max-width:760px;">
    <form method="post" action="{{ route('maintenance.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid cols-2">
            <div class="form-group" style="grid-column:1/-1;">
                <label for="title">Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
                <label for="property_id">Property *</label>
                <select name="property_id" id="property_id" required>
                    @foreach ($properties as $p)
                        <option value="{{ $p->id }}" @selected(old('property_id', request('property')) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="unit_id">Unit</label>
                <select name="unit_id" id="unit_id">
                    <option value="">(whole property)</option>
                    @foreach ($properties as $p)
                        @foreach ($p->units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id', request('unit')) == $u->id)>{{ $p->name }} / {{ $u->label }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="category">Category *</label>
                <select name="category" id="category">
                    @foreach (\App\Models\MaintenanceRequest::CATEGORIES as $k => $v)
                        <option value="{{ $k }}" @selected(old('category') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="priority">Priority *</label>
                <select name="priority" id="priority">
                    @foreach (\App\Models\MaintenanceRequest::PRIORITIES as $k => $v)
                        <option value="{{ $k }}" @selected(old('priority', 'medium') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="photo">Photo (optional, max 5MB)</label>
                <input type="file" name="photo" id="photo" accept="image/*">
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Create request</button>
            <a href="{{ route('maintenance.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
