@extends('layouts.app')
@section('title', 'Payments log')
@section('content')
<div class="card">
    <div class="row" style="margin-bottom:14px;">
        <strong>Payments ({{ $payments->total() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('rent-invoices.index') }}" class="btn secondary sm">Invoices</a>
    </div>
    @if ($payments->isEmpty())
        <div class="empty">No payments recorded yet.</div>
    @else
        <table>
            <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Invoice</th><th>Tenant</th></tr></thead>
            <tbody>
                @foreach ($payments as $p)
                    <tr>
                        <td>{{ $p->paid_on->format('Y-m-d') }}</td>
                        <td>${{ number_format((float) $p->amount, 2) }}</td>
                        <td class="muted">{{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }}</td>
                        <td><a href="{{ route('rent-invoices.show', $p->invoice) }}">{{ $p->invoice->invoice_number }}</a></td>
                        <td>{{ $p->invoice->lease?->tenant_name ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
