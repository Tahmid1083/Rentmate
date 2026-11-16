@extends('layouts.app')
@section('title', $property->name)
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $property->name }}</h2>
            <div class="muted">{{ $property->full_address }}</div>
            <div class="muted" style="margin-top:4px;">Type: {{ $property->type }}</div>
        </div>
        <span class="spacer"></span>
        <a href="{{ route('properties.edit', $property) }}" class="btn secondary">Edit</a>
        <a href="{{ route('properties.units.create', $property) }}" class="btn">+ Add unit</a>
    </div>
</div>

<div class="card">
    <div class="row" style="margin-bottom:12px;">
        <strong>Units ({{ $property->units->count() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('properties.units.create', $property) }}" class="btn sm">+ Add unit</a>
    </div>
    @if ($property->units->isEmpty())
        <div class="empty">No units yet. Add your first unit.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Unit</th><th>Floor</th><th>BR/BA</th><th>Market rent</th><th>Occupied</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($property->units as $u)
                    <tr>
                        <td><a href="{{ route('units.show', $u) }}">{{ $u->label }}</a></td>
                        <td class="muted">{{ $u->floor ?: '-' }}</td>
                        <td class="muted">{{ $u->bedrooms }} / {{ $u->bathrooms }}</td>
                        <td>${{ number_format((float) $u->market_rent, 2) }}</td>
                        <td>
                            @if ($u->is_occupied)
                                <span class="badge active">occupied</span>
                            @else
                                <span class="badge expired">vacant</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('units.show', $u) }}" class="btn secondary sm">View</a>
                            <form method="post" action="{{ route('units.destroy', $u) }}" style="display:inline;"
                                  onsubmit="return confirm('Deleting this unit also deletes its leases, invoices, payments, and maintenance requests. Continue?');">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
