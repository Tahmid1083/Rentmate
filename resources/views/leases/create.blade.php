@extends('layouts.app')
@section('title', 'New lease')
@section('content')
<div class="card" style="max-width:760px;">
    <form method="post" action="{{ route('leases.store') }}">
        @csrf
        <div class="grid cols-2">
            <div class="form-group" style="grid-column:1/-1;">
                <label for="unit_id">Unit *</label>
                <select name="unit_id" id="unit_id" required>
                    <option value="">Select a unit…</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id', request('unit')) == $u->id)>
                            {{ $u->property->name }} — {{ $u->label }} @if($u->is_occupied) (occupied) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="tenant_name">Tenant name *</label>
                <input type="text" name="tenant_name" id="tenant_name" value="{{ old('tenant_name') }}" required>
            </div>
            <div class="form-group">
                <label for="tenant_email">Tenant email</label>
                <input type="email" name="tenant_email" id="tenant_email" value="{{ old('tenant_email') }}">
            </div>
            <div class="form-group">
                <label for="tenant_phone">Tenant phone</label>
                <input type="tel" name="tenant_phone" id="tenant_phone" value="{{ old('tenant_phone') }}">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    @foreach (['active' => 'Active', 'upcoming' => 'Upcoming', 'expired' => 'Expired', 'terminated' => 'Terminated'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('status', 'active') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="lease_start">Lease start *</label>
                <input type="date" name="lease_start" id="lease_start" value="{{ old('lease_start', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label for="lease_end">Lease end</label>
                <input type="date" name="lease_end" id="lease_end" value="{{ old('lease_end') }}">
            </div>
            <div class="form-group">
                <label for="monthly_rent">Monthly rent ($) *</label>
                <input type="number" name="monthly_rent" id="monthly_rent" step="0.01" min="0" value="{{ old('monthly_rent') }}" required>
            </div>
            <div class="form-group">
                <label for="deposit">Deposit ($)</label>
                <input type="number" name="deposit" id="deposit" step="0.01" min="0" value="{{ old('deposit') }}">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="row">
            <button class="btn" type="submit">Create lease</button>
            <a href="{{ route('leases.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
