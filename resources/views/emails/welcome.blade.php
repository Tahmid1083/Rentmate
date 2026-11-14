<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Welcome</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;color:#1e293b;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
    <h2 style="margin-top:0;">Welcome to Rental Manager</h2>
    <p>Hi {{ $user->name }},</p>
    <p>Your landlord account is ready. You can now:</p>
    <ul>
        <li>Add your properties and units</li>
        <li>Track tenants and leases</li>
        <li>Issue rent invoices and record payments</li>
        <li>Log maintenance requests with photos</li>
        <li>Store private documents</li>
        <li>Run reports on income, expenses and occupancy</li>
    </ul>
    <p>Log in to get started.</p>
    <p style="color:#64748b;font-size:12px;">If you didn't create this account, you can safely ignore this email.</p>
</div>
</body>
</html>
