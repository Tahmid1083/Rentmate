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
                <label for="invoice_total">Invoice amount ($) *</label>
                <input type="number" name="invoice_total" id="invoice_total" step="0.01" min="0" value="{{ old('invoice_total', $rentInvoice->invoice_total) }}" required>
            </div>
            <div class="form-group">
                <label for="advance_credit">Advance credit ($)</label>
                <input type="number" name="advance_credit" id="advance_credit" step="0.01" min="0" value="{{ old('advance_credit', $rentInvoice->advance_credit) }}">
                <small class="muted">Deducted from the invoice amount; it is not recorded as a payment.</small>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('rent-invoices.show', $rentInvoice) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
