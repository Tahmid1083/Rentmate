<?php

namespace App\Console\Commands;

use App\Mail\InvoiceCreatedMail;
use App\Models\RentInvoice;
use App\Models\Setting;
use App\Models\TenantLease;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'rent:generate-invoices
                            {--month= : Target month (1-12). Defaults to current month.}
                            {--year=  : Target year. Defaults to current year.}
                            {--dry-run : Show what would be created without writing.}';

    protected $description = 'Auto-generate monthly rent invoices for every active lease that does not yet have one for the target month.';

    public function handle(): int
    {
        $now      = now();
        $month    = (int) $this->option('month') ?: $now->month;
        $year     = (int) $this->option('year')  ?: $now->year;
        $dryRun   = (bool) $this->option('dry-run');

        $leases = TenantLease::where('status', TenantLease::STATUS_ACTIVE)
            ->with(['unit.property.user.setting'])
            ->get();

        if ($leases->isEmpty()) {
            $this->info('No active leases found.');
            return self::SUCCESS;
        }

        $start = now()->create($year, $month, 1)->startOfMonth();
        $end   = now()->create($year, $month, 1)->endOfMonth();
        $due   = $start->copy()->addDays(5);

        $created = 0;
        $skipped = 0;

        foreach ($leases as $lease) {
            // Skip if an invoice already exists for this lease + month.
            $exists = RentInvoice::where('tenant_lease_id', $lease->id)
                ->whereYear('billing_period_start', $year)
                ->whereMonth('billing_period_start', $month)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("Would create invoice for {$lease->tenant_name} — {$lease->monthly_rent}");
                $created++;
                continue;
            }

            $invoice = RentInvoice::create([
                'tenant_lease_id'      => $lease->id,
                'unit_id'              => $lease->unit_id,
                'invoice_number'       => 'INV-' . $year . '-' . strtoupper(Str::random(8)),
                'billing_period_start' => $start,
                'billing_period_end'   => $end,
                'due_date'             => $due,
                'amount_due'           => $lease->monthly_rent,
                'status'               => RentInvoice::STATUS_OPEN,
            ]);

            $user = $lease->unit->property->user ?? null;
            if ($user) {
                $setting = Setting::for($user);
                if ($setting->notify_invoice_created && $user->email) {
                    Mail::to($user->email)->send(new InvoiceCreatedMail($invoice, $setting));
                }
            }

            $created++;
        }

        $this->info("Done. Invoices " . ($dryRun ? 'that would be created' : 'created') . ": {$created}. Already existed: {$skipped}.");

        return self::SUCCESS;
    }
}
