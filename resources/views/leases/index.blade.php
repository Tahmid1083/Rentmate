@extends('layouts.app')
@section('title', 'Leases')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Leases ({{ $leases->total() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('leases.create') }}" class="btn sm">+ New lease</a>
    </div>
    @if ($leases->isEmpty())
        <div class="empty">No leases yet.</div>
    @else
        <table>
            <thead>
                <tr><th>Tenant</th><th>Unit</th><th>Lease</th><th>Rent</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($leases as $l)
                    <tr>
                        <td>{{ $l->tenant_name }}</td>
                        <td class="muted">{{ $l->unit->property->name }} / {{ $l->unit->label }}</td>
                        <td class="muted">{{ $l->lease_start->format('Y-m-d') }} → {{ $l->lease_end?->format('Y-m-d') ?: 'open' }}</td>
                        <td>${{ number_format((float) $l->monthly_rent, 2) }}</td>
                        <td><span class="badge {{ $l->status }}">{{ $l->status }}</span></td>
                        <td><a href="{{ route('leases.show', $l) }}" class="btn secondary sm">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $leases->links() }}</div>
    @endif
</div>
@endsection
