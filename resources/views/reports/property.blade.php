@extends('layouts.app')
@section('title', 'Per-property P&L')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <form method="get" class="row" style="gap:8px;">
            <label class="muted" style="font-size:12px;align-self:center;">Year:</label>
            <select name="year" onchange="this.form.submit()">
                @for ($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                @endfor
            </select>
        </form>
        <span class="spacer"></span>
        <a href="{{ route('reports.index') }}" class="btn secondary sm">Back to overview</a>
    </div>

    @if ($breakdown->isEmpty())
        <div class="empty">No properties yet.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Property</th><th>Income</th><th>Expenses</th><th>Net</th><th>Occupancy</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($breakdown as $b)
                    <tr>
                        <td><a href="{{ route('properties.show', $b['property']) }}">{{ $b['property']->name }}</a></td>
                        <td style="color:var(--green);">${{ number_format($b['income'], 2) }}</td>
                        <td style="color:var(--red);">${{ number_format($b['expenses'], 2) }}</td>
                        <td style="{{ $b['net'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                            {{ $b['net'] >= 0 ? '$' : '-$' }}{{ number_format(abs($b['net']), 2) }}
                        </td>
                        <td class="muted">{{ $b['occupancy'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
