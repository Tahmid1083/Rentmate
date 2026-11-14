<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Payment received</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;color:#1e293b;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
    <h2 style="margin-top:0;color:#15803d;">Payment received</h2>
    <p>A payment has been recorded against invoice {{ $payment->invoice->invoice_number }}.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr><td style="padding:6px 0;color:#64748b;">Amount</td><td style="padding:6px 0;"><strong>${{ number_format((float) $payment->amount, 2) }}</strong></td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Method</td><td style="padding:6px 0;">{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Paid on</td><td style="padding:6px 0;">{{ $payment->paid_on->format('Y-m-d') }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Reference</td><td style="padding:6px 0;">{{ $payment->reference ?: '-' }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Invoice balance</td><td style="padding:6px 0;">${{ number_format((float) $payment->invoice->balance, 2) }}</td></tr>
    </table>
    <p style="color:#64748b;font-size:12px;">Logged automatically by your Rental Manager.</p>
</div>
</body>
</html>
