@extends('layouts.app')
@section('title', 'Rent & Invoices')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Invoices ({{ $invoices->total() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('payments.index') }}" class="btn secondary sm">Payments log</a>
        <a href="{{ route('rent-invoices.create') }}" class="btn sm">+ New invoice</a>
    </div>
    @if ($invoices->isEmpty())
        <div class="empty">No invoices yet.</div>
    @else
        <table>
            <thead>
                <tr><th>Invoice</th><th>Tenant</th><th>Unit</th><th>Due date</th><th>Invoice amount</th><th>Advance credit</th><th>Amount due</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach ($invoices as $inv)
                    <tr>
                        <td><a href="{{ route('rent-invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->lease?->tenant_name ?: '-' }}</td>
                        <td class="muted">{{ $inv->unit?->label ?: '-' }}</td>
                        <td>{{ $inv->due_date->format('Y-m-d') }}</td>
                        <td>${{ number_format((float) $inv->invoice_total, 2) }}</td>
                        <td>${{ number_format((float) $inv->advance_credit, 2) }}</td>
                        <td>${{ number_format((float) $inv->amount_due, 2) }}</td>
                        <td>${{ number_format((float) $inv->paid_amount, 2) }}</td>
                        <td>${{ number_format((float) $inv->balance, 2) }}</td>
                        <td><span class="badge {{ $inv->status }}">{{ $inv->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
