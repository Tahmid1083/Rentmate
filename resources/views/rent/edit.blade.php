@extends('layouts.app')
@section('title', 'Edit invoice ' . $rentInvoice->invoice_number)
@section('content')
<div class="card" style="max-width:680px;">
    <form method="post" action="{{ route('rent-invoices.update', $rentInvoice) }}">
        @csrf @method('PUT')
        <div class="grid cols-2">
            <div class="form-group">
                <label for="billing_period_start">Billing period start *</label>
                <input type="date" name="billing_period_start" id="billing_period_start" value="{{ old('billing_period_start', $rentInvoice->billing_period_start->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="billing_period_end">Billing period end *</label>
                <input type="date" name="billing_period_end" id="billing_period_end" value="{{ old('billing_period_end', $rentInvoice->billing_period_end->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="due_date">Due date *</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $rentInvoice->due_date->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="amount_due">Amount due ($) *</label>
                <input type="number" name="amount_due" id="amount_due" step="0.01" min="0" value="{{ old('amount_due', $rentInvoice->amount_due) }}" required>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('rent-invoices.show', $rentInvoice) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
