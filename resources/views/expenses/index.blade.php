@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Expenses ({{ $expenses->total() }})</strong>
        <span class="spacer"></span>
        <span class="muted" style="font-size:12px;">This month: <strong>${{ number_format((float) $totalThisMonth, 2) }}</strong></span>
        <a href="{{ route('expenses.export.csv') }}?{{ http_build_query(request()->only(['property_id','category','from','to'])) }}" class="btn secondary sm">Export CSV</a>
        <a href="{{ route('expenses.create') }}" class="btn sm">+ Add expense</a>
    </div>

    <form method="get" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px;">
        <select name="property_id">
            <option value="">All properties</option>
            @foreach ($properties as $p)
                <option value="{{ $p->id }}" @selected(request('property_id') == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="category">
            <option value="">All categories</option>
            @foreach (\App\Models\Expense::CATEGORIES as $k => $v)
                <option value="{{ $k }}" @selected(request('category') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" placeholder="From">
        <input type="date" name="to"   value="{{ request('to') }}" placeholder="To">
        <div style="grid-column:1/-1;display:flex;gap:6px;">
            <button class="btn sm" type="submit">Filter</button>
            <a href="{{ route('expenses.index') }}" class="btn secondary sm">Clear</a>
        </div>
    </form>

    @if ($expenses->isEmpty())
        <div class="empty">No expenses yet.</div>
    @else
        <table>
            <thead><tr><th>Date</th><th>Property</th><th>Category</th><th>Vendor</th><th>Amount</th><th></th></tr></thead>
            <tbody>
                @foreach ($expenses as $e)
                    <tr>
                        <td>{{ $e->incurred_on->format('Y-m-d') }}</td>
                        <td class="muted">{{ $e->property?->name ?: '—' }}@if($e->unit) / {{ $e->unit->label }}@endif</td>
                        <td>{{ \App\Models\Expense::CATEGORIES[$e->category] ?? $e->category }}</td>
                        <td class="muted">{{ $e->vendor ?: '—' }}</td>
                        <td>${{ number_format((float) $e->amount, 2) }}</td>
                        <td>
                            <a href="{{ route('expenses.show', $e) }}" class="btn secondary sm">View</a>
                            <a href="{{ route('expenses.edit', $e) }}" class="btn secondary sm">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $expenses->links() }}</div>
    @endif
</div>
@endsection
