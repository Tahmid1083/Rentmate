@extends('layouts.app')
@section('title', 'Properties')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Properties ({{ $properties->total() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('properties.create') }}" class="btn sm">+ Add property</a>
    </div>
    @if ($properties->isEmpty())
        <div class="empty">No properties yet. Click <strong>Add property</strong> to get started.</div>
    @else
        <table>
            <thead>
                <tr><th>Name</th><th>Address</th><th>Type</th><th>Units</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($properties as $p)
                    <tr>
                        <td><a href="{{ route('properties.show', $p) }}">{{ $p->name }}</a></td>
                        <td class="muted">{{ $p->full_address }}</td>
                        <td class="muted">{{ $p->type }}</td>
                        <td>{{ $p->units->count() }}</td>
                        <td>
                            <a href="{{ route('properties.edit', $p) }}" class="btn secondary sm">Edit</a>
                            <form method="post" action="{{ route('properties.destroy', $p) }}" style="display:inline;"
                                  onsubmit="return confirm('Delete this property and all its units?');">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $properties->links() }}</div>
    @endif
</div>
@endsection
