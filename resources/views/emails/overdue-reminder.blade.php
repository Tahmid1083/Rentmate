<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Overdue reminder</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;color:#1e293b;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
    <h2 style="margin-top:0;color:#b91c1c;">Invoice overdue</h2>
    <p>This is a reminder that invoice {{ $invoice->invoice_number }} is past due.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr><td style="padding:6px 0;color:#64748b;">Tenant</td><td style="padding:6px 0;">{{ $invoice->lease?->tenant_name }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Due date</td><td style="padding:6px 0;">{{ $invoice->due_date->format('Y-m-d') }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Outstanding balance</td><td style="padding:6px 0;"><strong>${{ number_format((float) $invoice->balance, 2) }}</strong></td></tr>
        @if ($lateFee = $invoice->computeLateFee($setting))
            @if ((float) $lateFee > 0)
                <tr><td style="padding:6px 0;color:#64748b;">Estimated late fee</td><td style="padding:6px 0;">${{ $lateFee }}</td></tr>
            @endif
        @endif
    </table>
    <p style="color:#64748b;font-size:12px;">Automated reminder from your Rental Manager.</p>
</div>
</body>
</html>
