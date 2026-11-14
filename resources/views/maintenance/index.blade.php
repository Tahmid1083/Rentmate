@extends('layouts.app')
@section('title', 'Maintenance')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Maintenance requests ({{ $requests->total() }})</strong>
        <span class="spacer"></span>
        @foreach (\App\Models\MaintenanceRequest::STATUSES as $k => $v)
            <a href="?status={{ $k }}" class="btn secondary sm">{{ $v }}</a>
        @endforeach
        <a href="{{ route('maintenance.create') }}" class="btn sm">+ New request</a>
    </div>
    @if ($requests->isEmpty())
        <div class="empty">No maintenance requests.</div>
    @else
        <table>
            <thead><tr><th>Title</th><th>Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
                @foreach ($requests as $m)
                    <tr>
                        <td><a href="{{ route('maintenance.show', $m) }}">{{ $m->title }}</a></td>
                        <td class="muted">{{ $m->unit?->label ?: $m->property?->name ?: '-' }}</td>
                        <td class="muted">{{ \App\Models\MaintenanceRequest::CATEGORIES[$m->category] ?? $m->category }}</td>
                        <td><span class="badge {{ $m->priority }}">{{ $m->priority }}</span></td>
                        <td><span class="badge {{ $m->status }}">{{ str_replace('_', ' ', $m->status) }}</span></td>
                        <td class="muted">{{ $m->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
