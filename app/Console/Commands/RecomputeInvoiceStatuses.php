<?php

namespace App\Console\Commands;

use App\Models\RentInvoice;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Console\Command;

class RecomputeInvoiceStatuses extends Command
{
    protected $signature = 'rent:recompute-statuses';

    protected $description = 'Recompute every invoice status (open / partial / paid / overdue) based on current date and payments.';

    public function handle(): int
    {
        $count = 0;
        RentInvoice::chunkById(200, function ($invoices) use (&$count) {
            foreach ($invoices as $invoice) {
                $invoice->recomputeStatus();
                $count++;
            }
        });

        $this->info("Recomputed {$count} invoices.");
        return self::SUCCESS;
    }
}
