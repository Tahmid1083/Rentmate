@extends('layouts.app')
@section('title', 'Upload document')
@section('content')
<div class="card" style="max-width:560px;">
    <form method="post" action="{{ route('documents.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="file">File *</label>
            <input type="file" name="file" id="file" required>
            <div class="muted" style="font-size:12px;margin-top:4px;">Max 15 MB. Stored privately.</div>
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea name="description" id="description" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="row">
            <button class="btn" type="submit">Upload</button>
            <a href="{{ route('documents.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
