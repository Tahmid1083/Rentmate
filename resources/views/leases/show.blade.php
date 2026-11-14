@extends('layouts.app')
@section('title', 'Lease — ' . $lease->tenant_name)
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $lease->tenant_name }}</h2>
            <div class="muted">{{ $lease->unit->property->name }} — Unit {{ $lease->unit->label }}</div>
        </div>
        <span class="spacer"></span>
        <span class="badge {{ $lease->status }}">{{ $lease->status }}</span>
        <a href="{{ route('leases.edit', $lease) }}" class="btn secondary">Edit</a>
        <form method="post" action="{{ route('leases.destroy', $lease) }}" style="display:inline;"
              onsubmit="return confirm('Delete this lease and its invoices?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    <dl class="kv" style="margin-top:16px;">
        <dt>Email</dt><dd>{{ $lease->tenant_email ?: '-' }}</dd>
        <dt>Phone</dt><dd>{{ $lease->tenant_phone ?: '-' }}</dd>
        <dt>Lease</dt><dd>{{ $lease->lease_start->format('Y-m-d') }} → {{ $lease->lease_end?->format('Y-m-d') ?: 'open-ended' }}</dd>
        <dt>Monthly rent</dt><dd>${{ number_format((float) $lease->monthly_rent, 2) }}</dd>
        <dt>Deposit</dt><dd>${{ number_format((float) $lease->deposit, 2) }}</dd>
        <dt>Notes</dt><dd>{{ $lease->notes ?: '-' }}</dd>
    </dl>
</div>

<div class="card">
    <div class="row" style="margin-bottom:12px;">
        <strong>Rent invoices ({{ $lease->rentInvoices->count() }})</strong>
        <span class="spacer"></span>
        <a href="{{ route('rent-invoices.create') }}?lease={{ $lease->id }}" class="btn sm">+ New invoice</a>
    </div>
    @if ($lease->rentInvoices->isEmpty())
        <div class="empty">No invoices yet.</div>
    @else
        <table>
            <thead><tr><th>Invoice</th><th>Period</th><th>Due</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($lease->rentInvoices as $inv)
                    <tr>
                        <td><a href="{{ route('rent-invoices.show', $inv) }}">{{ $inv->invoice_number }}</a></td>
                        <td class="muted">{{ $inv->billing_period_start->format('Y-m') }}</td>
                        <td>{{ $inv->due_date->format('Y-m-d') }}</td>
                        <td>${{ number_format((float) $inv->amount_due, 2) }}</td>
                        <td>${{ number_format((float) $inv->paid_amount, 2) }}</td>
                        <td>${{ number_format((float) $inv->balance, 2) }}</td>
                        <td><span class="badge {{ $inv->status }}">{{ $inv->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
