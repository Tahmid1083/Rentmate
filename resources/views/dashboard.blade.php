@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<?php
    $user = auth()->user();
    $setting = \App\Models\Setting::for($user);
    $monthIncome   = (float) $collectedThisMonth;
    $monthExpense  = \App\Models\Expense::where('user_id', $user->id)
                        ->whereMonth('incurred_on', now()->month)
                        ->whereYear('incurred_on', now()->year)
                        ->sum('amount');
    $netMonth      = $monthIncome - (float) $monthExpense;
?>

<div class="grid cols-4" style="margin-bottom:18px;">
    <div class="card stat">
        <div class="label">Expected monthly rent</div>
        <div class="value">${{ number_format((float) $expectedRent, 2) }}</div>
        <div class="sub">Sum of active leases</div>
    </div>
    <div class="card stat">
        <div class="label">Collected this month</div>
        <div class="value">${{ number_format($monthIncome, 2) }}</div>
        <div class="sub">{{ now()->format('M Y') }} payments</div>
    </div>
    <div class="card stat">
        <div class="label">Expenses this month</div>
        <div class="value" style="color:var(--red);">${{ number_format((float) $monthExpense, 2) }}</div>
        <div class="sub">Net: <span style="color:{{ $netMonth >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $netMonth >= 0 ? '$' : '-$' }}{{ number_format(abs($netMonth), 2) }}</span></div>
    </div>
    <div class="card stat">
        <div class="label">Outstanding balance</div>
        <div class="value" style="color:var(--amber);">${{ number_format((float) $outstanding, 2) }}</div>
        <div class="sub">Across all invoices</div>
    </div>
</div>

<div class="grid cols-2">
    <div class="card">
        <div class="row" style="margin-bottom:12px;">
            <strong>Open invoices</strong>
            <span class="spacer"></span>
            <a href="{{ route('rent-invoices.index') }}" class="btn secondary sm">View all</a>
        </div>
        @if ($recentInvoices->isEmpty())
            <div class="empty">No open invoices.</div>
        @else
            <table>
                <thead>
                    <tr><th>Invoice</th><th>Due</th><th>Amount</th><th>Status</th><th>Late fee</th></tr>
                </thead>
                <tbody>
                    @foreach ($recentInvoices as $inv)
                        <tr>
                            <td><a href="{{ route('rent-invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                            <td>{{ $inv->due_date->format('Y-m-d') }}</td>
                            <td>${{ number_format((float) $inv->amount_due, 2) }}</td>
                            <td><span class="badge {{ $inv->status }}">{{ str_replace('_', ' ', $inv->status) }}</span></td>
                            <td class="muted">${{ $inv->computeLateFee($setting) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <div class="row" style="margin-bottom:12px;">
            <strong>Recent maintenance</strong>
            <span class="spacer"></span>
            <a href="{{ route('maintenance.index') }}" class="btn secondary sm">View all</a>
        </div>
        @if ($recentMaintenance->isEmpty())
            <div class="empty">No maintenance requests yet.</div>
        @else
            <table>
                <thead>
                    <tr><th>Title</th><th>Priority</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($recentMaintenance as $m)
                        <tr>
                            <td><a href="{{ route('maintenance.show', $m) }}">{{ $m->title }}</a></td>
                            <td><span class="badge {{ $m->priority }}">{{ $m->priority }}</span></td>
                            <td><span class="badge {{ $m->status }}">{{ str_replace('_', ' ', $m->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="card" style="margin-top:18px;">
    <div class="row" style="margin-bottom:12px;">
        <strong>Your portfolio ({{ $properties->count() }} properties, {{ $unitCount }} units)</strong>
        <span class="spacer"></span>
        <a href="{{ route('reports.index') }}" class="btn secondary sm">Reports &amp; analytics</a>
        <a href="{{ route('properties.create') }}" class="btn sm">+ Add property</a>
    </div>
    @if ($properties->isEmpty())
        <div class="empty">You haven't added any properties yet.</div>
    @else
        <table>
            <thead>
                <tr><th>Property</th><th>Address</th><th>Units</th><th>Occupied</th><th>Occupancy</th></tr>
            </thead>
            <tbody>
                @foreach ($properties as $p)
                    <?php
                        $total = $p->units->count();
                        $occupied = $p->units->where('is_occupied', true)->count();
                        $rate = $total ? round($occupied / $total * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><a href="{{ route('properties.show', $p) }}">{{ $p->name }}</a></td>
                        <td class="muted">{{ $p->full_address }}</td>
                        <td>{{ $total }}</td>
                        <td>{{ $occupied }}</td>
                        <td style="min-width:160px;">
                            <div class="progress"><span style="width:{{ $rate }}%;"></span></div>
                            <div class="muted" style="font-size:11px;margin-top:2px;">{{ $rate }}%</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card" style="margin-top:18px;">
    <strong>Quick actions</strong>
    <div class="row" style="margin-top:10px;flex-wrap:wrap;">
        <a href="{{ route('properties.create') }}" class="btn">+ Property</a>
        <a href="{{ route('leases.create') }}" class="btn secondary">+ Lease</a>
        <a href="{{ route('rent-invoices.create') }}" class="btn secondary">+ Invoice</a>
        <a href="{{ route('maintenance.create') }}" class="btn secondary">+ Maintenance</a>
        <a href="{{ route('expenses.create') }}" class="btn secondary">+ Expense</a>
        <a href="{{ route('documents.create') }}" class="btn secondary">+ Document</a>
    </div>
</div>
@endsection
