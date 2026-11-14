<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rental Management')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --bg: #0f172a;
            --panel: #1e293b;
            --panel-2: #273449;
            --border: #334155;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --primary: #38bdf8;
            --primary-2: #0ea5e9;
            --green: #4ade80;
            --amber: #fbbf24;
            --red: #f87171;
            --purple: #a78bfa;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            font-size: 14px;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 240px; background: var(--panel); padding: 20px 14px;
            border-right: 1px solid var(--border);
            flex-shrink: 0;
        }
        .sidebar .brand {
            font-weight: 700; font-size: 18px; margin-bottom: 24px;
            color: #fff; display: flex; align-items: center; gap: 8px;
        }
        .sidebar .brand .dot {
            width: 10px; height: 10px; background: var(--primary);
            border-radius: 2px; display: inline-block;
        }
        .nav a {
            display: block; padding: 8px 12px; color: var(--muted);
            border-radius: 6px; margin-bottom: 2px; font-size: 13px;
        }
        .nav a:hover { background: var(--panel-2); color: var(--text); text-decoration: none; }
        .nav a.active { background: var(--panel-2); color: var(--primary); }
        .main { flex: 1; padding: 28px 36px; overflow-x: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .topbar h1 { margin: 0; font-size: 24px; color: #fff; font-weight: 600; }
        .topbar .actions { display: flex; gap: 8px; align-items: center; }
        .user-chip { color: var(--muted); font-size: 13px; }
        .grid { display: grid; gap: 16px; }
        .grid.cols-2 { grid-template-columns: 1fr 1fr; }
        .grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 900px) {
            .grid.cols-2, .grid.cols-3, .grid.cols-4 { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
        }
        .stat .label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .stat .value { font-size: 26px; color: #fff; font-weight: 700; margin-top: 4px; }
        .stat .sub { color: var(--muted); font-size: 12px; margin-top: 2px; }
        .btn {
            display: inline-block; padding: 8px 14px; border-radius: 6px;
            background: var(--primary); color: #0b1220; font-weight: 600;
            border: none; cursor: pointer; font-size: 13px;
            text-decoration: none;
        }
        .btn:hover { background: var(--primary-2); text-decoration: none; }
        .btn.secondary { background: var(--panel-2); color: var(--text); border: 1px solid var(--border); }
        .btn.secondary:hover { background: var(--border); }
        .btn.danger { background: var(--red); color: #fff; }
        .btn.sm { padding: 4px 10px; font-size: 12px; }
        .btn.success { background: var(--green); color: #07140b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .03em; }
        td { color: var(--text); }
        tr:hover td { background: var(--panel-2); }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            font-size: 11px; font-weight: 600; text-transform: capitalize;
        }
        .badge.open { background: rgba(56,189,248,.15); color: var(--primary); }
        .badge.paid { background: rgba(74,222,128,.15); color: var(--green); }
        .badge.partial { background: rgba(251,191,36,.15); color: var(--amber); }
        .badge.overdue { background: rgba(248,113,113,.15); color: var(--red); }
        .badge.active { background: rgba(74,222,128,.15); color: var(--green); }
        .badge.upcoming { background: rgba(167,139,250,.15); color: var(--purple); }
        .badge.expired, .badge.terminated { background: rgba(148,163,184,.15); color: var(--muted); }
        .badge.in_progress { background: rgba(56,189,248,.15); color: var(--primary); }
        .badge.on_hold { background: rgba(251,191,36,.15); color: var(--amber); }
        .badge.completed { background: rgba(74,222,128,.15); color: var(--green); }
        .badge.cancelled { background: rgba(148,163,184,.15); color: var(--muted); }
        .badge.low { background: rgba(148,163,184,.15); color: var(--muted); }
        .badge.medium { background: rgba(56,189,248,.15); color: var(--primary); }
        .badge.high { background: rgba(251,191,36,.15); color: var(--amber); }
        .badge.urgent { background: rgba(248,113,113,.15); color: var(--red); }
        .form-group { margin-bottom: 14px; }
        label { display: block; color: var(--muted); margin-bottom: 4px; font-size: 12px; }
        input[type=text], input[type=email], input[type=password], input[type=number],
        input[type=date], input[type=tel], select, textarea {
            width: 100%; padding: 8px 10px; background: var(--panel-2);
            border: 1px solid var(--border); border-radius: 6px;
            color: var(--text); font-size: 14px;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--primary);
        }
        .flash {
            padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;
            background: rgba(56,189,248,.1); border: 1px solid rgba(56,189,248,.3);
            color: var(--primary);
        }
        .flash.error { background: rgba(248,113,113,.1); border-color: rgba(248,113,113,.3); color: var(--red); }
        .muted { color: var(--muted); }
        .row { display: flex; gap: 10px; align-items: center; }
        .spacer { flex: 1; }
        .empty { color: var(--muted); padding: 30px 0; text-align: center; }
        .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .auth-card { width: 100%; max-width: 380px; }
        .auth-card h1 { font-size: 22px; margin: 0 0 6px; color: #fff; }
        .auth-card p.sub { color: var(--muted); margin: 0 0 22px; font-size: 13px; }
        .pagination { margin-top: 18px; display: flex; gap: 6px; }
        .pagination a, .pagination span {
            padding: 6px 10px; border-radius: 6px; background: var(--panel-2);
            color: var(--text); font-size: 13px;
        }
        .pagination .active { background: var(--primary); color: #0b1220; }
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li { padding: 10px 0; border-bottom: 1px solid var(--border); }
        .timeline li:last-child { border-bottom: 0; }
        .timeline .event { color: var(--primary); font-weight: 600; font-size: 13px; text-transform: capitalize; }
        .timeline .when { color: var(--muted); font-size: 11px; }
        .timeline .note { color: var(--text); font-size: 13px; margin-top: 2px; }
        img.photo { max-width: 100%; border-radius: 8px; border: 1px solid var(--border); }
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 9px 12px; border-radius: 6px;
            background: #fff; color: #1f2937; font-weight: 600; font-size: 13px;
            border: 1px solid #d1d5db; cursor: pointer;
        }
        .btn-google:hover { background: #f9fafb; text-decoration: none; }
        .btn-google svg { width: 18px; height: 18px; }
        .divider { display: flex; align-items: center; gap: 10px; margin: 14px 0; color: var(--muted); font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .chart-bar-row { display: flex; align-items: flex-end; gap: 6px; height: 160px; padding-top: 6px; }
        .chart-bar { flex: 1; background: var(--primary); border-radius: 4px 4px 0 0; min-height: 2px; position: relative; }
        .chart-bar.expense { background: var(--red); }
        .chart-bar-row .label { font-size: 11px; color: var(--muted); text-align: center; }
        .chart-axis { display: flex; gap: 6px; margin-top: 4px; }
        .chart-axis span { flex: 1; text-align: center; font-size: 11px; color: var(--muted); }
        .progress { background: var(--panel-2); border-radius: 6px; height: 8px; overflow: hidden; }
        .progress > span { display: block; height: 100%; background: var(--green); }
        .kv { display: grid; grid-template-columns: 130px 1fr; gap: 8px 14px; }
        .kv dt { color: var(--muted); font-size: 12px; }
        .kv dd { margin: 0; color: var(--text); }
    </style>
</head>
<body>
@if (!isset($hideLayout))
<div class="layout">
    <aside class="sidebar">
        <div class="brand"><span class="dot"></span> Rental Manager</div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('properties.index') }}" class="{{ request()->routeIs('properties.*') ? 'active' : '' }}">Properties</a>
            <a href="{{ route('leases.index') }}" class="{{ request()->routeIs('leases.*') ? 'active' : '' }}">Leases</a>
            <a href="{{ route('rent-invoices.index') }}" class="{{ request()->routeIs('rent-invoices.*', 'payments.*') ? 'active' : '' }}">Rent &amp; Invoices</a>
            <a href="{{ route('maintenance.index') }}" class="{{ request()->routeIs('maintenance.*') ? 'active' : '' }}">Maintenance</a>
            <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">Expenses</a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">Documents</a>
            <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">Settings</a>
        </nav>
    </aside>
    <main class="main">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="actions">
                <span class="user-chip">{{ auth()->user()->name ?? '' }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn secondary sm" type="submit">Sign out</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
@else
    @if (session('success')) <div class="flash" style="max-width:380px;margin:0 auto 12px;">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="flash error" style="max-width:380px;margin:0 auto 12px;">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="flash error" style="max-width:380px;margin:0 auto 12px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
@endif
</body>
</html>
