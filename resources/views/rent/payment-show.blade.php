@extends('layouts.app')
@section('title', 'Payment detail')
@section('content')
<div class="card">
    <h2 style="margin-top:0;">Payment of ${{ number_format((float) $payment->amount, 2) }}</h2>
    <dl class="kv">
        <dt>Date</dt><dd>{{ $payment->paid_on->format('Y-m-d') }}</dd>
        <dt>Method</dt><dd>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</dd>
        <dt>Reference</dt><dd>{{ $payment->reference ?: '-' }}</dd>
        <dt>Invoice</dt><dd><a href="{{ route('rent-invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a></dd>
        <dt>Notes</dt><dd>{{ $payment->notes ?: '-' }}</dd>
    </dl>
    <div class="row" style="margin-top:14px;">
        <a href="{{ route('rent-invoices.show', $payment->invoice) }}" class="btn secondary">Back to invoice</a>
    </div>
</div>
@endsection
