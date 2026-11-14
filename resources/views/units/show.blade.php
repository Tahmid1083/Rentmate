@extends('layouts.app')
@section('title', $unit->property->name . ' — ' . $unit->label)
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $unit->label }}</h2>
            <div class="muted">{{ $unit->property->name }} — {{ $unit->property->full_address }}</div>
        </div>
        <span class="spacer"></span>
        <a href="{{ route('units.edit', $unit) }}" class="btn secondary">Edit</a>
        <form method="post" action="{{ route('units.destroy', $unit) }}" style="display:inline;"
              onsubmit="return confirm('Delete this unit and all related data?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    <dl class="kv" style="margin-top:16px;">
        <dt>Floor</dt><dd>{{ $unit->floor ?: '-' }}</dd>
        <dt>Bedrooms</dt><dd>{{ $unit->bedrooms }}</dd>
        <dt>Bathrooms</dt><dd>{{ $unit->bathrooms }}</dd>
        <dt>Square feet</dt><dd>{{ $unit->square_feet ?: '-' }}</dd>
        <dt>Market rent</dt><dd>${{ number_format((float) $unit->market_rent, 2) }}</dd>
        <dt>Occupancy</dt>
        <dd>
            @if ($unit->is_occupied) <span class="badge active">occupied</span> @else <span class="badge expired">vacant</span> @endif
        </dd>
    </dl>
</div>

<div class="grid cols-2">
    <div class="card">
        <div class="row" style="margin-bottom:12px;">
            <strong>Active lease</strong>
            <span class="spacer"></span>
            <a href="{{ route('leases.create') }}?unit={{ $unit->id }}" class="btn sm">+ New lease</a>
        </div>
        @if ($unit->activeLease)
            <dl class="kv">
                <dt>Tenant</dt><dd>{{ $unit->activeLease->tenant_name }}</dd>
                <dt>Email</dt><dd>{{ $unit->activeLease->tenant_email ?: '-' }}</dd>
                <dt>Phone</dt><dd>{{ $unit->activeLease->tenant_phone ?: '-' }}</dd>
                <dt>Lease</dt><dd>{{ $unit->activeLease->lease_start->format('Y-m-d') }} → {{ $unit->activeLease->lease_end?->format('Y-m-d') ?: 'open' }}</dd>
                <dt>Monthly rent</dt><dd>${{ number_format((float) $unit->activeLease->monthly_rent, 2) }}</dd>
                <dt>Deposit</dt><dd>${{ number_format((float) $unit->activeLease->deposit, 2) }}</dd>
            </dl>
            <a href="{{ route('leases.show', $unit->activeLease) }}" class="btn secondary sm" style="margin-top:10px;">View lease</a>
        @else
            <div class="empty">No active lease. <a href="{{ route('leases.create') }}?unit={{ $unit->id }}">Create one</a>.</div>
        @endif
    </div>

    <div class="card">
        <div class="row" style="margin-bottom:12px;">
            <strong>Maintenance ({{ $unit->maintenanceRequests->count() }})</strong>
            <span class="spacer"></span>
            <a href="{{ route('maintenance.create') }}?unit={{ $unit->id }}" class="btn sm">+ New request</a>
        </div>
        @if ($unit->maintenanceRequests->isEmpty())
            <div class="empty">No maintenance requests.</div>
        @else
            <table>
                <thead><tr><th>Title</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($unit->maintenanceRequests->take(8) as $m)
                        <tr>
                            <td><a href="{{ route('maintenance.show', $m) }}">{{ $m->title }}</a></td>
                            <td><span class="badge {{ $m->status }}">{{ str_replace('_', ' ', $m->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
