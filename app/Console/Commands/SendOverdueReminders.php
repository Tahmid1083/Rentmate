<?php

namespace App\Console\Commands;

use App\Mail\OverdueReminderMail;
use App\Models\RentInvoice;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueReminders extends Command
{
    protected $signature = 'rent:overdue-reminders
                            {--dry-run : Show what would be sent without sending.}';

    protected $description = 'Send overdue-reminder emails for every overdue/partial invoice whose landlord opted in.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $invoices = RentInvoice::whereIn('status', [
                RentInvoice::STATUS_OVERDUE,
                RentInvoice::STATUS_PARTIAL,
            ])
            ->where('due_date', '<', now())
            ->with(['lease.unit.property.user.setting'])
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($invoices as $invoice) {
            $user = $invoice->lease?->unit?->property?->user ?? null;
            if (! $user) continue;

            $setting = Setting::for($user);
            if (! $setting->notify_overdue_reminder || ! $user->email) {
                continue;
            }

            if ($dryRun) {
                $this->line("Would send reminder for {$invoice->invoice_number} to {$user->email}");
                $sent++;
                continue;
            }

            Mail::to($user->email)->send(new OverdueReminderMail($invoice, $setting));
            $sent++;
        }

        $this->info("Done. Reminders " . ($dryRun ? 'that would be sent' : 'sent') . ": {$sent}.");

        return self::SUCCESS;
    }
}
