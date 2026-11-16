<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceCreatedMail;
use App\Models\RentInvoice;
use App\Models\Setting;
use App\Models\TenantLease;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RentInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = RentInvoice::query()
            ->whereHas('unit.property', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with(['lease.unit.property'])
            ->latest()
            ->paginate(20);

        return view('rent.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $leases = $this->landlordLeases($request);

        return view('rent.create', compact('leases'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_lease_id'      => ['required', 'exists:tenant_leases,id'],
            'billing_period_start' => ['required', 'date'],
            'billing_period_end'   => ['required', 'date', 'after_or_equal:billing_period_start'],
            'due_date'             => ['required', 'date'],
            'invoice_total'        => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'advance_credit'       => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2', 'lte:invoice_total'],
        ]);

        $lease = TenantLease::with('unit')->findOrFail($data['tenant_lease_id']);
        $this->authorizeLease($request, $lease);

        $data = $this->applyAdvanceCredit($data);
        $data['unit_id']        = $lease->unit_id;
        $data['invoice_number'] = $this->generateInvoiceNumber();
        $data['status']         = (float) $data['amount_due'] <= 0
            ? RentInvoice::STATUS_PAID
            : RentInvoice::STATUS_OPEN;

        $invoice = RentInvoice::create($data);

        // Email the landlord (if they opted in).
        $user = $lease->unit->property->user;
        if ($user) {
            $setting = Setting::for($user);
            if ($setting->notify_invoice_created && $user->email) {
                Mail::to($user->email)->send(new InvoiceCreatedMail($invoice, $setting));
            }
        }

        return redirect()->route('rent-invoices.show', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function show(Request $request, RentInvoice $rentInvoice)
    {
        $this->authorizeInvoice($request, $rentInvoice);

        $rentInvoice->load(['lease.unit.property', 'payments']);

        return view('rent.show', compact('rentInvoice'));
    }

    public function edit(Request $request, RentInvoice $rentInvoice)
    {
        $this->authorizeInvoice($request, $rentInvoice);

        $leases = $this->landlordLeases($request);

        return view('rent.edit', compact('rentInvoice', 'leases'));
    }

    public function update(Request $request, RentInvoice $rentInvoice)
    {
        $this->authorizeInvoice($request, $rentInvoice);

        $data = $request->validate([
            'billing_period_start' => ['required', 'date'],
            'billing_period_end'   => ['required', 'date', 'after_or_equal:billing_period_start'],
            'due_date'             => ['required', 'date'],
            'invoice_total'        => [
                'required',
                'numeric',
                'min:0',
                'max:99999999.99',
                'decimal:0,2',
                function ($attribute, $value, $fail) use ($request, $rentInvoice) {
                    $advanceCredit = $request->input('advance_credit', 0);
                    if (
                        is_numeric($advanceCredit)
                        && (float) $value - (float) $advanceCredit < (float) $rentInvoice->paid_amount
                    ) {
                        $fail('The amount due after advance cannot be less than payments already recorded.');
                    }
                },
            ],
            'advance_credit'       => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2', 'lte:invoice_total'],
        ]);

        $data = $this->applyAdvanceCredit($data);
        $rentInvoice->update($data);
        $rentInvoice->recomputeStatus();

        return redirect()->route('rent-invoices.show', $rentInvoice)
            ->with('success', 'Invoice updated.');
    }

    public function destroy(Request $request, RentInvoice $rentInvoice)
    {
        $this->authorizeInvoice($request, $rentInvoice);

        $rentInvoice->delete();

        return redirect()->route('rent-invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->year . '-' . strtoupper(Str::random(8));
    }

    protected function applyAdvanceCredit(array $data): array
    {
        $data['invoice_total'] = number_format((float) $data['invoice_total'], 2, '.', '');
        $data['advance_credit'] = number_format((float) ($data['advance_credit'] ?? 0), 2, '.', '');
        $data['amount_due'] = bcsub($data['invoice_total'], $data['advance_credit'], 2);

        return $data;
    }

    protected function landlordLeases(Request $request)
    {
        return TenantLease::whereHas('unit.property', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('unit.property')->latest()->get();
    }

    protected function authorizeLease(Request $request, TenantLease $lease): void
    {
        if ($lease->unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizeInvoice(Request $request, RentInvoice $invoice): void
    {
        if ($invoice->unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
