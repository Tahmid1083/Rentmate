@extends('layouts.app')
@section('title', 'Edit lease')
@section('content')
<div class="card" style="max-width:760px;">
    <form method="post" action="{{ route('leases.update', $lease) }}">
        @csrf @method('PUT')
        <div class="grid cols-2">
            <div class="form-group" style="grid-column:1/-1;">
                <label for="unit_id">Unit *</label>
                <select name="unit_id" id="unit_id" required>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id', (string) $lease->unit_id) === (string) $u->id)>
                            {{ $u->property->name }} — {{ $u->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="tenant_name">Tenant name *</label>
                <input type="text" name="tenant_name" id="tenant_name" value="{{ old('tenant_name', $lease->tenant_name) }}" required>
            </div>
            <div class="form-group">
                <label for="tenant_email">Tenant email</label>
                <input type="email" name="tenant_email" id="tenant_email" value="{{ old('tenant_email', $lease->tenant_email) }}">
            </div>
            <div class="form-group">
                <label for="tenant_phone">Tenant phone</label>
                <input type="tel" name="tenant_phone" id="tenant_phone" value="{{ old('tenant_phone', $lease->tenant_phone) }}">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    @foreach (['active' => 'Active', 'upcoming' => 'Upcoming', 'expired' => 'Expired', 'terminated' => 'Terminated'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('status', $lease->status) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="lease_start">Lease start *</label>
                <input type="date" name="lease_start" id="lease_start" value="{{ old('lease_start', $lease->lease_start->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="lease_end">Lease end</label>
                <input type="date" name="lease_end" id="lease_end" value="{{ old('lease_end', $lease->lease_end?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label for="monthly_rent">Monthly rent ($) *</label>
                <input type="number" name="monthly_rent" id="monthly_rent" step="0.01" min="0" value="{{ old('monthly_rent', $lease->monthly_rent) }}" required>
            </div>
            <div class="form-group">
                <label for="deposit">Deposit ($)</label>
                <input type="number" name="deposit" id="deposit" step="0.01" min="0" value="{{ old('deposit', $lease->deposit) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $lease->notes) }}</textarea>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Save changes</button>
            <a href="{{ route('leases.show', $lease) }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
