@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<form method="post" action="{{ route('settings.update') }}">
    @csrf @method('PUT')

    <div class="card" style="margin-bottom:18px;">
        <h3 style="margin-top:0;">Business profile</h3>
        <p class="muted" style="font-size:12px;margin-top:-6px;">Shown on invoices and email notifications.</p>
        <div class="grid cols-2">
            <div class="form-group">
                <label for="business_name">Business name</label>
                <input type="text" name="business_name" id="business_name" value="{{ old('business_name', $setting->business_name) }}">
            </div>
            <div class="form-group">
                <label for="business_email">Business email</label>
                <input type="email" name="business_email" id="business_email" value="{{ old('business_email', $setting->business_email) }}">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $setting->phone) }}">
            </div>
            <div class="form-group">
                <label for="currency">Currency</label>
                <select name="currency" id="currency">
                    @foreach ($currencies as $code => $label)
                        <option value="{{ $code }}" @selected(old('currency', $setting->currency) === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="timezone">Timezone</label>
                <input type="text" name="timezone" id="timezone" value="{{ old('timezone', $setting->timezone) }}">
            </div>
            <div class="form-group">
                <label for="locale">Locale</label>
                <input type="text" name="locale" id="locale" value="{{ old('locale', $setting->locale) }}">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:18px;">
        <h3 style="margin-top:0;">Late fee policy</h3>
        <p class="muted" style="font-size:12px;margin-top:-6px;">Applied automatically on overdue invoices.</p>
        <div class="grid cols-3">
            <div class="form-group">
                <label for="late_fee_grace_days">Grace days</label>
                <input type="number" name="late_fee_grace_days" id="late_fee_grace_days" min="0" max="60" value="{{ old('late_fee_grace_days', $setting->late_fee_grace_days) }}">
            </div>
            <div class="form-group">
                <label for="late_fee_flat">Flat late fee ($)</label>
                <input type="number" name="late_fee_flat" id="late_fee_flat" step="0.01" min="0" value="{{ old('late_fee_flat', $setting->late_fee_flat) }}">
            </div>
            <div class="form-group">
                <label for="late_fee_percent">Percent of outstanding (%)</label>
                <input type="number" name="late_fee_percent" id="late_fee_percent" step="0.01" min="0" max="100" value="{{ old('late_fee_percent', $setting->late_fee_percent) }}">
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:18px;">
        <h3 style="margin-top:0;">Email notifications</h3>
        <p class="muted" style="font-size:12px;margin-top:-6px;">Sent to your landlord email. Configure SMTP in <code>.env</code>.</p>
        <div class="grid cols-2">
            @foreach ([
                'notify_invoice_created'    => 'New invoice created',
                'notify_payment_received'   => 'Payment received',
                'notify_maintenance_status' => 'Maintenance status changed',
                'notify_lease_expiring'     => 'Lease expiring soon (30 days)',
                'notify_overdue_reminder'   => 'Overdue invoice reminders',
            ] as $key => $label)
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, (bool) $setting->$key)) style="width:auto;">
                        {{ $label }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Account</h3>
        <dl class="kv">
            <dt>Name</dt><dd>{{ auth()->user()->name }}</dd>
            <dt>Email</dt><dd>{{ auth()->user()->email }}</dd>
            <dt>Sign-in</dt>
            <dd>
                @if (auth()->user()->provider === 'google')
                    <span class="badge active">Google linked</span>
                @else
                    <span class="badge expired">Email + password</span>
                @endif
            </dd>
        </dl>
    </div>

    <div class="row" style="margin-top:18px;">
        <button class="btn" type="submit">Save settings</button>
    </div>
</form>
@endsection
