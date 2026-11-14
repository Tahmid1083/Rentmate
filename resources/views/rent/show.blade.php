@extends('layouts.app')
@section('title', 'Invoice ' . $rentInvoice->invoice_number)
@section('content')
<div class="card" style="margin-bottom:18px;">
    <div class="row">
        <div>
            <h2 style="margin:0 0 6px;">{{ $rentInvoice->invoice_number }}</h2>
            <div class="muted">{{ $rentInvoice->lease?->tenant_name }} — {{ $rentInvoice->unit?->property?->name }} / {{ $rentInvoice->unit?->label }}</div>
        </div>
        <span class="spacer"></span>
        <span class="badge {{ $rentInvoice->status }}">{{ $rentInvoice->status }}</span>
        <a href="{{ route('rent-invoices.edit', $rentInvoice) }}" class="btn secondary">Edit</a>
        <form method="post" action="{{ route('rent-invoices.destroy', $rentInvoice) }}" style="display:inline;"
              onsubmit="return confirm('Delete this invoice and all its payments?');">
            @csrf @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
    <div class="grid cols-3" style="margin-top:16px;">
        <div class="stat">
            <div class="label">Amount due</div>
            <div class="value">${{ number_format((float) $rentInvoice->amount_due, 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Paid</div>
            <div class="value" style="color:var(--green);">${{ number_format((float) $rentInvoice->paid_amount, 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Balance</div>
            <div class="value" style="color:var(--amber);">${{ number_format((float) $rentInvoice->balance, 2) }}</div>
        </div>
    </div>
    <dl class="kv" style="margin-top:16px;">
        <dt>Billing period</dt><dd>{{ $rentInvoice->billing_period_start->format('Y-m-d') }} → {{ $rentInvoice->billing_period_end->format('Y-m-d') }}</dd>
        <dt>Due date</dt><dd>{{ $rentInvoice->due_date->format('Y-m-d') }}</dd>
    </dl>
</div>

<div class="card">
    <div class="row" style="margin-bottom:12px;">
        <strong>Payments ({{ $rentInvoice->payments->count() }})</strong>
        <span class="spacer"></span>
        @if ($rentInvoice->status !== \App\Models\RentInvoice::STATUS_PAID)
            <button class="btn sm" type="button"
                    onclick="document.getElementById('payment-form').style.display='block';">+ Record payment</button>
        @endif
    </div>

    <div id="payment-form" style="display:none;background:var(--panel-2);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:14px;">
        <form method="post" action="{{ route('rent-invoices.payments.store', $rentInvoice) }}">
            @csrf
            <div class="grid cols-3">
                <div class="form-group">
                    <label for="amount">Amount ($) *</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                           value="{{ number_format((float) $rentInvoice->balance, 2, '.', '') }}" required>
                </div>
                <div class="form-group">
                    <label for="paid_on">Paid on *</label>
                    <input type="date" name="paid_on" id="paid_on" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="method">Method</label>
                    <select name="method" id="method">
                        @foreach (\App\Models\Payment::METHODS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label for="reference">Reference (check # / txn id)</label>
                    <input type="text" name="reference" id="reference">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" rows="2"></textarea>
                </div>
            </div>
            <div class="row">
                <button class="btn success" type="submit">Record payment</button>
                <button type="button" class="btn secondary"
                        onclick="document.getElementById('payment-form').style.display='none';">Cancel</button>
            </div>
        </form>
    </div>

    @if ($rentInvoice->payments->isEmpty())
        <div class="empty">No payments recorded yet.</div>
    @else
        <table>
            <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr></thead>
            <tbody>
                @foreach ($rentInvoice->payments as $p)
                    <tr>
                        <td>{{ $p->paid_on->format('Y-m-d') }}</td>
                        <td>${{ number_format((float) $p->amount, 2) }}</td>
                        <td class="muted">{{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }}</td>
                        <td class="muted">{{ $p->reference ?: '-' }}</td>
                        <td>
                            <form method="post" action="{{ route('payments.destroy', $p) }}" style="display:inline;"
                                  onsubmit="return confirm('Remove this payment?');">
                                @csrf @method('DELETE')
                                <button class="btn danger sm" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
