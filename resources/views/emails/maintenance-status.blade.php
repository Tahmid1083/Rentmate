<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Maintenance update</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;color:#1e293b;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
    <h2 style="margin-top:0;">Maintenance status update</h2>
    <p><strong>{{ $request->title }}</strong></p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr><td style="padding:6px 0;color:#64748b;">Property</td><td style="padding:6px 0;">{{ $request->property?->name }}@if($request->unit) / {{ $request->unit->label }}@endif</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">Previous status</td><td style="padding:6px 0;">{{ str_replace('_', ' ', $oldStatus) }}</td></tr>
        <tr><td style="padding:6px 0;color:#64748b;">New status</td><td style="padding:6px 0;"><strong>{{ str_replace('_', ' ', $newStatus) }}</strong></td></tr>
        @if ($note)<tr><td style="padding:6px 0;color:#64748b;">Note</td><td style="padding:6px 0;">{{ $note }}</td></tr>@endif
    </table>
    <p style="color:#64748b;font-size:12px;">Logged automatically by your Rental Manager.</p>
</div>
</body>
</html>
