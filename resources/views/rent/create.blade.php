@extends('layouts.app')
@section('title', 'New invoice')
@section('content')
@if ($leases->isEmpty())
    <div class="card" style="max-width:680px;">
        <div class="empty">
            <p>No tenant leases are available to invoice yet.</p>
            <p>Add a property and a unit, then create a tenant lease before creating an invoice.</p>
            <div class="row" style="justify-content:center;">
                <a href="{{ route('properties.create') }}" class="btn">+ Add property</a>
                <a href="{{ route('leases.create') }}" class="btn secondary">Create lease</a>
            </div>
        </div>
    </div>
@else
<div class="card" style="max-width:680px;">
    <form method="post" action="{{ route('rent-invoices.store') }}">
        @csrf
        <div class="form-group">
            <label for="tenant_lease_id">Lease *</label>
            <select name="tenant_lease_id" id="tenant_lease_id" required>
                <option value="">Select a lease…</option>
                @foreach ($leases as $l)
                    <option value="{{ $l->id }}" @selected(old('tenant_lease_id', request('lease')) == $l->id)>
                        {{ $l->tenant_name }} — {{ $l->unit->property->name }} / {{ $l->unit->label }}
                        (${{ number_format((float) $l->monthly_rent, 2) }}/mo)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="grid cols-2">
            <div class="form-group">
                <label for="billing_period_start">Billing period start *</label>
                <input type="date" name="billing_period_start" id="billing_period_start" value="{{ old('billing_period_start', date('Y-m-01')) }}" required>
            </div>
            <div class="form-group">
                <label for="billing_period_end">Billing period end *</label>
                <input type="date" name="billing_period_end" id="billing_period_end" value="{{ old('billing_period_end', date('Y-m-t')) }}" required>
            </div>
            <div class="form-group">
                <label for="due_date">Due date *</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-05')) }}" required>
            </div>
            <div class="form-group">
                <label for="invoice_total">Invoice amount ($) *</label>
                <input type="number" name="invoice_total" id="invoice_total" step="0.01" min="0" value="{{ old('invoice_total') }}" required>
            </div>
            <div class="form-group">
                <label for="advance_credit">Advance credit ($)</label>
                <input type="number" name="advance_credit" id="advance_credit" step="0.01" min="0" value="{{ old('advance_credit', '0.00') }}">
                <small class="muted">Deducted from the invoice amount; it is not recorded as a payment.</small>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Create invoice</button>
            <a href="{{ route('rent-invoices.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endif
@endsection
