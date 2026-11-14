@extends('layouts.app')
@section('title', 'Expense — ' . ($expense->description ?: $expense->vendor ?: $expense->category))
@section('content')
<div class="card">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">${{ number_format((float) $expense->amount, 2) }}</h2>
            <div class="muted">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }} &middot; {{ $expense->incurred_on->format('Y-m-d') }}</div>
        </div>
        <span class="spacer"></span>
        <a href="{{ route('expenses.edit', $expense) }}" class="btn secondary">Edit</a>
        <form method="post" action="{{ route('expenses.destroy', $expense) }}" style="display:inline;"
              onsubmit="return confirm('Delete this expense?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    <dl class="kv" style="margin-top:16px;">
        <dt>Property</dt><dd>{{ $expense->property?->name ?: '—' }}</dd>
        <dt>Unit</dt><dd>{{ $expense->unit?->label ?: '—' }}</dd>
        <dt>Vendor</dt><dd>{{ $expense->vendor ?: '—' }}</dd>
        <dt>Description</dt><dd>{{ $expense->description ?: '—' }}</dd>
        <dt>Recurring</dt><dd>{{ $expense->is_recurring ? 'Yes — monthly' : 'No' }}</dd>
        <dt>Receipt</dt><dd>
            @if ($expense->receipt_path)
                <em class="muted">(stored privately — add a download link if you want)</em>
            @else
                —
            @endif
        </dd>
    </dl>
</div>
@endsection
