@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <form method="get" class="row" style="gap:8px;">
            <label class="muted" style="font-size:12px;align-self:center;">Year:</label>
            <select name="year" onchange="this.form.submit()">
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected((int) request('year', now()->year) === $y)>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <span class="spacer"></span>
        <a href="{{ route('reports.property') }}?year={{ $year }}" class="btn secondary sm">Per-property P&L</a>
    </div>
</div>

<div class="grid cols-4" style="margin-bottom:18px;">
    <div class="card stat">
        <div class="label">Income ({{ $year }})</div>
        <div class="value" style="color:var(--green);">${{ number_format($totalIncome, 2) }}</div>
    </div>
    <div class="card stat">
        <div class="label">Expenses ({{ $year }})</div>
        <div class="value" style="color:var(--red);">${{ number_format($totalExpense, 2) }}</div>
    </div>
    <div class="card stat">
        <div class="label">Net cash flow</div>
        <div class="value">{{ $netCashflow >= 0 ? '$' . number_format($netCashflow, 2) : '-$' . number_format(abs($netCashflow), 2) }}</div>
        <div class="sub">{{ $netCashflow >= 0 ? 'Profit' : 'Loss' }}</div>
    </div>
    <div class="card stat">
        <div class="label">Occupancy</div>
        <div class="value">{{ $occupancyRate }}%</div>
        <div class="sub">{{ $occupiedCount }} / {{ $totalUnits }} units</div>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="row" style="margin-bottom:12px;">
        <strong>Monthly income vs. expenses ({{ $year }})</strong>
        <span class="spacer"></span>
        <span class="muted" style="font-size:12px;">
            <span style="display:inline-block;width:10px;height:10px;background:var(--primary);border-radius:2px;"></span> Income
            &nbsp;&nbsp;
            <span style="display:inline-block;width:10px;height:10px;background:var(--red);border-radius:2px;"></span> Expenses
        </span>
    </div>
    <?php
        $maxVal = max(1, max(array_merge($monthlyIncome, $monthlyExpense)));
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    ?>
    <div class="chart-bar-row">
        @for ($i = 0; $i < 12; $i++)
            <div style="flex:1;display:flex;gap:2px;align-items:flex-end;height:100%;">
                <div class="chart-bar" style="height:{{ ($monthlyIncome[$i] / $maxVal) * 100 }}%;" title="Income {{ $months[$i] }}: ${{ number_format($monthlyIncome[$i], 2) }}"></div>
                <div class="chart-bar expense" style="height:{{ ($monthlyExpense[$i] / $maxVal) * 100 }}%;" title="Expense {{ $months[$i] }}: ${{ number_format($monthlyExpense[$i], 2) }}"></div>
            </div>
        @endfor
    </div>
    <div class="chart-axis">
        @foreach ($months as $m) <span>{{ $m }}</span> @endforeach
    </div>
</div>

<div class="grid cols-2">
    <div class="card">
        <strong>Expense breakdown by category ({{ $year }})</strong>
        @if ($expenseBreakdown->isEmpty())
            <div class="empty">No expenses recorded in {{ $year }}.</div>
        @else
            <table style="margin-top:10px;">
                <thead><tr><th>Category</th><th>Total</th><th>%</th></tr></thead>
                <tbody>
                    @php $expTotal = $expenseBreakdown->sum(); @endphp
                    @foreach ($expenseBreakdown as $cat => $total)
                        <tr>
                            <td>{{ \App\Models\Expense::CATEGORIES[$cat] ?? ucfirst($cat) }}</td>
                            <td>${{ number_format((float) $total, 2) }}</td>
                            <td class="muted">{{ $expTotal > 0 ? round(((float) $total / $expTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <strong>Maintenance breakdown by status</strong>
        @if ($maintenanceBreakdown->isEmpty())
            <div class="empty">No maintenance activity.</div>
        @else
            <table style="margin-top:10px;">
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    @foreach ($maintenanceBreakdown as $status => $count)
                        <tr>
                            <td><span class="badge {{ $status }}">{{ str_replace('_', ' ', $status) }}</span></td>
                            <td>{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="grid cols-2" style="margin-top:18px;">
    <div class="card">
        <strong>Leases expiring in the next 30 days</strong>
        @if ($expiringLeases->isEmpty())
            <div class="empty">No leases expiring soon.</div>
        @else
            <table style="margin-top:10px;">
                <thead><tr><th>Tenant</th><th>Unit</th><th>Ends</th></tr></thead>
                <tbody>
                    @foreach ($expiringLeases as $l)
                        <tr>
                            <td><a href="{{ route('leases.show', $l) }}">{{ $l->tenant_name }}</a></td>
                            <td class="muted">{{ $l->unit->property->name }} / {{ $l->unit->label }}</td>
                            <td>{{ $l->lease_end->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <strong>Top 10 outstanding balances</strong>
        @if ($topOutstanding->isEmpty())
            <div class="empty">No outstanding invoices.</div>
        @else
            <table style="margin-top:10px;">
                <thead><tr><th>Invoice</th><th>Tenant</th><th>Balance</th></tr></thead>
                <tbody>
                    @foreach ($topOutstanding as $inv)
                        <tr>
                            <td><a href="{{ route('rent-invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                            <td class="muted">{{ $inv->lease?->tenant_name }}</td>
                            <td style="color:var(--amber);">${{ number_format((float) $inv->balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
