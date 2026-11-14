@extends('layouts.app')
@section('title', $document->name)
@section('content')
<div class="card">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $document->name }}</h2>
            <div class="muted">{{ number_format($document->size / 1024, 1) }} KB &middot; {{ $document->mime_type ?: 'unknown' }} &middot; uploaded {{ $document->created_at->format('Y-m-d') }}</div>
        </div>
        <span class="spacer"></span>
        <a href="{{ route('documents.download', $document) }}" class="btn">Download</a>
        <form method="post" action="{{ route('documents.destroy', $document) }}" style="display:inline;"
              onsubmit="return confirm('Delete this document?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    @if ($document->description)
        <div style="margin-top:14px;">
            <strong>Description</strong>
            <p style="margin:6px 0 0;">{{ $document->description }}</p>
        </div>
    @endif
</div>
@endsection
