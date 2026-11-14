<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---------------------------------------------------------------------------
// Scheduled tasks for the rental management system.
//
// These only run if you have a system cron calling `php artisan schedule:run`
// every minute. On shared hosts / local dev you can also run them manually:
//   php artisan rent:generate-invoices
//   php artisan rent:overdue-reminders
//   php artisan rent:recompute-statuses
// ---------------------------------------------------------------------------

Schedule::command('rent:recompute-statuses')->dailyAt('00:30');
Schedule::command('rent:generate-invoices')->monthlyOn(1, '00:00');
Schedule::command('rent:overdue-reminders')->dailyAt('08:00');
