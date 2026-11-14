<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Operational summary: total expected rent, collected, outstanding,
     * open maintenance requests, plus per-property breakdown.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Pull the landlord's properties and units in two queries.
        $properties = Property::with(['units.activeLease'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $unitIds = $properties->flatMap->units->pluck('id')->all();

        // When the landlord has no units yet, every whereIn([]) would
        // match ALL rows in the table — guard against that explicitly.
        if (empty($unitIds)) {
            return view('dashboard', [
                'properties'         => $properties,
                'unitCount'          => 0,
                'expectedRent'       => 0,
                'collectedThisMonth' => 0,
                'outstanding'        => 0,
                'openMaintenance'    => 0,
                'recentMaintenance'  => collect(),
                'recentInvoices'     => collect(),
            ]);
        }

        // Expected rent = sum of monthly_rent for every active lease.
        $expectedRent = DB::table('tenant_leases')
            ->whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->sum('monthly_rent');

        // Collected this month = sum of payments made this month.
        $collectedThisMonth = DB::table('payments')
            ->whereIn('rent_invoice_id', function ($q) use ($unitIds) {
                $q->select('id')
                  ->from('rent_invoices')
                  ->whereIn('unit_id', $unitIds);
            })
            ->whereMonth('paid_on', now()->month)
            ->whereYear('paid_on', now()->year)
            ->sum('amount');

        // Outstanding balance across all invoices = (sum of amount_due) − (sum of payments).
        $totalDue = DB::table('rent_invoices')->whereIn('unit_id', $unitIds)->sum('amount_due');
        $totalPaid = DB::table('payments')
            ->whereIn('rent_invoice_id', function ($q) use ($unitIds) {
                $q->select('id')->from('rent_invoices')->whereIn('unit_id', $unitIds);
            })
            ->sum('amount');
        $outstanding = bcsub((string) $totalDue, (string) $totalPaid, 2);

        $openMaintenance = MaintenanceRequest::query()
            ->whereIn('unit_id', $unitIds)
            ->whereIn('status', [
                MaintenanceRequest::STATUS_OPEN,
                MaintenanceRequest::STATUS_IN_PROGRESS,
                MaintenanceRequest::STATUS_ON_HOLD,
            ])
            ->count();

        // Recent maintenance activity.
        $recentMaintenance = MaintenanceRequest::query()
            ->whereIn('unit_id', $unitIds)
            ->latest()
            ->limit(5)
            ->get();

        // Recent overdue / open invoices.
        $recentInvoices = RentInvoice::query()
            ->whereIn('unit_id', $unitIds)
            ->whereIn('status', [
                RentInvoice::STATUS_OPEN,
                RentInvoice::STATUS_PARTIAL,
                RentInvoice::STATUS_OVERDUE,
            ])
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'properties'         => $properties,
            'unitCount'          => count($unitIds),
            'expectedRent'       => $expectedRent,
            'collectedThisMonth' => $collectedThisMonth,
            'outstanding'        => $outstanding,
            'openMaintenance'    => $openMaintenance,
            'recentMaintenance'  => $recentMaintenance,
            'recentInvoices'     => $recentInvoices,
        ]);
    }
}
