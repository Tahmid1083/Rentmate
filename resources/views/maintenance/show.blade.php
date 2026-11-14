@extends('layouts.app')
@section('title', $maintenanceRequest->title)
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $maintenanceRequest->title }}</h2>
            <div class="muted">
                {{ $maintenanceRequest->property?->name ?? '—' }}
                @if ($maintenanceRequest->unit) / {{ $maintenanceRequest->unit->label }} @endif
                &middot; Created {{ $maintenanceRequest->created_at->format('Y-m-d') }}
            </div>
        </div>
        <span class="spacer"></span>
        <span class="badge {{ $maintenanceRequest->priority }}">{{ $maintenanceRequest->priority }}</span>
        <span class="badge {{ $maintenanceRequest->status }}">{{ str_replace('_', ' ', $maintenanceRequest->status) }}</span>
        <a href="{{ route('maintenance.edit', $maintenanceRequest) }}" class="btn secondary">Edit</a>
        <form method="post" action="{{ route('maintenance.destroy', $maintenanceRequest) }}" style="display:inline;"
              onsubmit="return confirm('Delete this maintenance request?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    <dl class="kv" style="margin-top:16px;">
        <dt>Category</dt><dd>{{ \App\Models\MaintenanceRequest::CATEGORIES[$maintenanceRequest->category] ?? $maintenanceRequest->category }}</dd>
        <dt>Reporter</dt><dd>{{ $maintenanceRequest->reporter?->name ?: '—' }}</dd>
        <dt>Completed</dt><dd>{{ $maintenanceRequest->completed_at?->format('Y-m-d H:i') ?: '—' }}</dd>
    </dl>
    @if ($maintenanceRequest->description)
        <div style="margin-top:14px;">
            <strong>Description</strong>
            <p style="margin:6px 0 0;color:var(--text);white-space:pre-wrap;">{{ $maintenanceRequest->description }}</p>
        </div>
    @endif
    @if ($maintenanceRequest->photo_path)
        <div style="margin-top:14px;">
            <strong>Photo</strong><br>
            <img class="photo" src="{{ asset('storage/' . $maintenanceRequest->photo_path) }}" alt="Maintenance photo" style="margin-top:6px;max-width:480px;">
        </div>
    @endif
</div>

<div class="grid cols-2">
    <div class="card">
        <strong>Update status</strong>
        <form method="post" action="{{ route('maintenance.update-status', $maintenanceRequest) }}" style="margin-top:10px;">
            @csrf @method('PATCH')
            <div class="form-group">
                <label for="status">New status</label>
                <select name="status" id="status">
                    @foreach (\App\Models\MaintenanceRequest::STATUSES as $k => $v)
                        <option value="{{ $k }}" @selected($maintenanceRequest->status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="note">Note (optional)</label>
                <textarea name="note" id="note" rows="2"></textarea>
            </div>
            <button class="btn" type="submit">Apply</button>
        </form>
    </div>

    <div class="card">
        <strong>Timeline</strong>
        <ul class="timeline" style="margin-top:10px;">
            @forelse ($maintenanceRequest->timeline as $t)
                <li>
                    <div class="event">{{ str_replace('_', ' ', $t->event) }}</div>
                    <div class="when">{{ $t->created_at->format('Y-m-d H:i') }} — {{ $t->user?->name ?: 'system' }}</div>
                    @if ($t->note) <div class="note">{{ $t->note }}</div> @endif
                </li>
            @empty
                <li class="muted">No activity yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
