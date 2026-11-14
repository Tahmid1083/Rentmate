@extends('layouts.app')
@section('title', 'Documents')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Documents ({{ $documents->total() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('documents.create') }}" class="btn sm">+ Upload document</a>
    </div>
    <div class="muted" style="margin-bottom:10px;font-size:12px;">
        Documents are stored privately and can only be downloaded by you.
    </div>
    @if ($documents->isEmpty())
        <div class="empty">No documents uploaded yet.</div>
    @else
        <table>
            <thead><tr><th>Name</th><th>Size</th><th>Type</th><th>Uploaded</th><th></th></tr></thead>
            <tbody>
                @foreach ($documents as $doc)
                    <tr>
                        <td><a href="{{ route('documents.show', $doc) }}">{{ $doc->name }}</a></td>
                        <td class="muted">{{ number_format($doc->size / 1024, 1) }} KB</td>
                        <td class="muted">{{ $doc->mime_type ?: '—' }}</td>
                        <td class="muted">{{ $doc->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('documents.download', $doc) }}" class="btn secondary sm">Download</a>
                            <form method="post" action="{{ route('documents.destroy', $doc) }}" style="display:inline;"
                                  onsubmit="return confirm('Delete this document?');">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $documents->links() }}</div>
    @endif
</div>
@endsection
