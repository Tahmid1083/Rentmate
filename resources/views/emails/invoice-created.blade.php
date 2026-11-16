<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New invoice</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;color:#1e293b;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
    <h2 style="margin-top:0;">New rent invoice</h2>
    <p>Hello {{ $setting->business_name ?: $invoice->lease?->tenant_name ?: 'there' }},</p>
    <p>A new rent invoice has been issued.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr><td style="padding:6px 0;color:#64748b;">Invoice</td><td style="padding:6px 0;">{{ $invoice->invoice_number }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Tenant</td><td style="padding:6px 0;">{{ $invoice->lease?->tenant_name }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Unit</td><td style="padding:6px 0;">{{ $invoice->unit?->property?->name }} / {{ $invoice->unit?->label }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Billing period</td><td style="padding:6px 0;">{{ $invoice->billing_period_start->format('Y-m-d') }} → {{ $invoice->billing_period_end->format('Y-m-d') }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Due date</td><td style="padding:6px 0;">{{ $invoice->due_date->format('Y-m-d') }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Invoice amount</td><td style="padding:6px 0;">${{ number_format((float) $invoice->invoice_total, 2) }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Advance credit</td><td style="padding:6px 0;">−${{ number_format((float) $invoice->advance_credit, 2) }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Amount due</td><td style="padding:6px 0;"><strong>${{ number_format((float) $invoice->amount_due, 2) }}</strong></td></tr>
    </table>
    <p style="color:#64748b;font-size:12px;">This is an automated message from your Rental Manager.</p>
</div>
</body>
</html>
