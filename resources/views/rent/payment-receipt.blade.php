@extends('layouts.app')
@section('title', 'Payment receipt')
@section('content')
<style>
    @media print {
        :root { --text: #111; --muted: #555; --panel: #fff; --border: #ddd; }
        body { background: #fff; color: #111; }
        .sidebar, .topbar, .receipt-actions { display: none !important; }
        .main { padding: 0; overflow: visible; }
        .card { background: #fff; color: #111; border: 0; }
        .receipt-muted, .kv dt { color: #555 !important; }
        .kv dd, .receipt-heading { color: #111 !important; }
    }
</style>
<div class="card" style="max-width:760px;margin:0 auto;">
    <div class="row receipt-actions" style="margin-bottom:18px;">
        <a href="{{ route('payments.show', $payment) }}" class="btn secondary">Back to payment</a>
        <span class="spacer"></span>
        <button type="button" class="btn" onclick="window.print()">Print receipt</button>
    </div>

    <div class="row" style="align-items:flex-start;margin-bottom:24px;">
        <div>
            <h2 class="receipt-heading" style="margin:0;">Payment receipt</h2>
            <div class="receipt-muted muted">{{ $setting->business_name ?: auth()->user()->name }}</div>
            @if ($setting->business_email)
                <div class="receipt-muted muted">{{ $setting->business_email }}</div>
            @endif
            @if ($setting->phone)
                <div class="receipt-muted muted">{{ $setting->phone }}</div>
            @endif
        </div>
        <span class="spacer"></span>
        <div style="text-align:right;">
            <strong>Receipt #{{ $payment->id }}</strong><br>
            <span class="receipt-muted muted">{{ $payment->paid_on->format('F j, Y') }}</span>
        </div>
    </div>

    <hr style="border:0;border-top:1px solid var(--border);">

    <div class="grid cols-2" style="margin:20px 0;">
        <div>
            <div class="receipt-muted muted" style="font-size:12px;text-transform:uppercase;">Received from</div>
            <strong>{{ $payment->invoice->lease?->tenant_name ?: 'Tenant' }}</strong>
            @if ($payment->invoice->lease?->tenant_email)
                <div class="receipt-muted muted">{{ $payment->invoice->lease->tenant_email }}</div>
            @endif
        </div>
        <div>
            <div class="receipt-muted muted" style="font-size:12px;text-transform:uppercase;">Rental property</div>
            <strong>{{ $payment->invoice->unit->property->name }}</strong>
            <div class="receipt-muted muted">{{ $payment->invoice->unit->label }}</div>
            <div class="receipt-muted muted">{{ $payment->invoice->unit->property->full_address }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>Description</th><th>Invoice</th><th>Payment method</th><th style="text-align:right;">Amount</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Rent payment</td>
                <td>{{ $payment->invoice->invoice_number }}</td>
                <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                <td style="text-align:right;">{{ $setting->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align:right;margin-top:18px;font-size:18px;">
        <strong>Total received: {{ $setting->currency }} {{ number_format((float) $payment->amount, 2) }}</strong>
    </div>

    @if ($payment->reference || $payment->notes)
        <dl class="kv" style="margin-top:24px;">
            @if ($payment->reference)
                <dt>Reference</dt><dd>{{ $payment->reference }}</dd>
            @endif
            @if ($payment->notes)
                <dt>Notes</dt><dd>{{ $payment->notes }}</dd>
            @endif
        </dl>
    @endif

    <p class="receipt-muted muted" style="margin-top:30px;text-align:center;">Thank you. This receipt confirms the payment shown above.</p>
</div>
@endsection
