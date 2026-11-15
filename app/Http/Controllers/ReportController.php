<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\TenantLease;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Full analytics view: monthly income vs. expenses for the last 12 months,
     * occupancy rate, net cash flow, expense breakdown by category,
     * expiring leases, top tenants by outstanding balance.
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $year  = (int) $request->get('year', now()->year);

        $properties = Property::where('user_id', $user->id)->with('units')->get();
        $propertyIds = $properties->pluck('id')->all();
        $unitIds     = $properties->flatMap->units->pluck('id')->all();

        // Empty portfolio → safe defaults so the page doesn't crash.
        if (empty($unitIds) && empty($propertyIds)) {
            return view('reports.index', [
                'year'           => $year,
                'years'          => collect([now()->year, now()->year - 1, now()->year - 2]),
                'monthlyIncome'  => array_fill(0, 12, 0),
                'monthlyExpense' => array_fill(0, 12, 0),
                'expenseBreakdown' => collect(),
                'occupancyRate'  => 0,
                'occupiedCount'  => 0,
                'totalUnits'     => 0,
                'netCashflow'    => 0,
                'totalIncome'    => 0,
                'totalExpense'   => 0,
                'expiringLeases' => collect(),
                'topOutstanding' => collect(),
                'maintenanceBreakdown' => collect(),
            ]);
        }

        // ----- Monthly income (payments) for selected year -----
        $incomeRows = DB::table('payments')
            ->whereIn('rent_invoice_id', function ($q) use ($unitIds) {
                $q->select('id')->from('rent_invoices')->whereIn('unit_id', $unitIds);
            })
            ->whereYear('paid_on', $year)
            ->get(['paid_on', 'amount']);

        $monthlyIncome = array_fill(0, 12, 0);
        foreach ($incomeRows as $row) {
            $month = Carbon::parse($row->paid_on)->month - 1;
            $monthlyIncome[$month] += (float) $row->amount;
        }

        // ----- Monthly expenses for selected year -----
        $expenseRows = DB::table('expenses')
            ->where('user_id', $user->id)
            ->whereYear('incurred_on', $year)
            ->get(['incurred_on', 'amount']);

        $monthlyExpense = array_fill(0, 12, 0);
        foreach ($expenseRows as $row) {
            $month = Carbon::parse($row->incurred_on)->month - 1;
            $monthlyExpense[$month] += (float) $row->amount;
        }

        // ----- Expense breakdown by category -----
        $expenseBreakdown = DB::table('expenses')
            ->selectRaw('category, SUM(amount) AS total')
            ->where('user_id', $user->id)
            ->whereYear('incurred_on', $year)
            ->groupBy('category')
            ->pluck('total', 'category');

        // ----- Maintenance breakdown by status -----
        $maintenanceBreakdown = DB::table('maintenance_requests')
            ->selectRaw('status, COUNT(*) AS cnt')
            ->where(function ($q) use ($propertyIds, $unitIds) {
                $q->whereIn('property_id', $propertyIds)
                  ->orWhereIn('unit_id', $unitIds);
            })
            ->groupBy('status')
            ->pluck('cnt', 'status');

        // ----- Occupancy rate -----
        $totalUnits   = count($unitIds);
        $occupiedCount = $properties->flatMap->units->where('is_occupied', true)->count();
        $occupancyRate = $totalUnits ? round($occupiedCount / $totalUnits * 100, 1) : 0;

        // ----- Lease expiring in next 30 days -----
        $expiringLeases = TenantLease::whereIn('unit_id', $unitIds)
            ->where('status', TenantLease::STATUS_ACTIVE)
            ->whereNotNull('lease_end')
            ->whereBetween('lease_end', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->with('unit.property')
            ->orderBy('lease_end')
            ->get();

        // ----- Top 10 outstanding balances -----
        $topOutstanding = RentInvoice::whereIn('unit_id', $unitIds)
            ->whereIn('status', [
                RentInvoice::STATUS_OPEN,
                RentInvoice::STATUS_PARTIAL,
                RentInvoice::STATUS_OVERDUE,
            ])
            ->with('lease', 'unit.property')
            ->orderByRaw('(amount_due - (SELECT COALESCE(SUM(amount),0) FROM payments WHERE rent_invoice_id = rent_invoices.id)) DESC')
            ->limit(10)
            ->get();

        $totalIncome  = array_sum($monthlyIncome);
        $totalExpense = array_sum($monthlyExpense);
        $netCashflow  = $totalIncome - $totalExpense;

        $years = collect(range(now()->year, now()->year - 4))->sort()->values();

        return view('reports.index', compact(
            'year', 'years',
            'monthlyIncome', 'monthlyExpense',
            'expenseBreakdown', 'maintenanceBreakdown',
            'occupancyRate', 'occupiedCount', 'totalUnits',
            'netCashflow', 'totalIncome', 'totalExpense',
            'expiringLeases', 'topOutstanding'
        ));
    }

    /**
     * Per-property P&L breakdown for the selected year.
     */
    public function propertyBreakdown(Request $request)
    {
        $user = $request->user();
        $year = (int) $request->get('year', now()->year);

        $properties = Property::where('user_id', $user->id)->with('units')->get();

        $breakdown = $properties->map(function (Property $p) use ($year) {
            $unitIds = $p->units->pluck('id')->all();

            $income = empty($unitIds) ? 0 :
                DB::table('payments')
                    ->whereIn('rent_invoice_id', function ($q) use ($unitIds) {
                        $q->select('id')->from('rent_invoices')->whereIn('unit_id', $unitIds);
                    })
                    ->whereYear('paid_on', $year)
                    ->sum('amount');

            $expenses = DB::table('expenses')
                ->where('user_id', $p->user_id)
                ->where(function ($q) use ($p, $unitIds) {
                    $q->where('property_id', $p->id)
                      ->orWhereIn('unit_id', empty($unitIds) ? [0] : $unitIds);
                })
                ->whereYear('incurred_on', $year)
                ->sum('amount');

            return [
                'property'    => $p,
                'income'      => (float) $income,
                'expenses'    => (float) $expenses,
                'net'         => (float) $income - (float) $expenses,
                'occupancy'   => $p->units->count()
                    ? round($p->units->where('is_occupied', true)->count() / $p->units->count() * 100, 1)
                    : 0,
            ];
        });

        return view('reports.property', compact('breakdown', 'year'));
    }
}
