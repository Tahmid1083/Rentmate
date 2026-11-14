@extends('layouts.app')
@section('title', 'Units')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Units in {{ $property->name }}</strong>
        <span class="spacer"></span>
        <a href="{{ route('properties.units.create', $property) }}" class="btn sm">+ Add unit</a>
    </div>
    @if ($units->isEmpty())
        <div class="empty">No units in this property yet.</div>
    @else
        <table>
            <thead><tr><th>Label</th><th>BR/BA</th><th>Market rent</th><th>Occupied</th><th></th></tr></thead>
            <tbody>
                @foreach ($units as $u)
                    <tr>
                        <td><a href="{{ route('units.show', $u) }}">{{ $u->label }}</a></td>
                        <td class="muted">{{ $u->bedrooms }} / {{ $u->bathrooms }}</td>
                        <td>${{ number_format((float) $u->market_rent, 2) }}</td>
                        <td>
                            @if ($u->is_occupied) <span class="badge active">occupied</span> @else <span class="badge expired">vacant</span> @endif
                        </td>
                        <td><a href="{{ route('units.show', $u) }}" class="btn secondary sm">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
