<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceivedMail;
use App\Models\Payment;
use App\Models\RentInvoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use League\Csv\Writer;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->whereHas('invoice.unit.property', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with(['invoice.unit.property'])
            ->latest()
            ->paginate(20);

        return view('rent.payments', compact('payments'));
    }

    public function store(Request $request, RentInvoice $rentInvoice)
    {
        $this->authorizeInvoice($request, $rentInvoice);

        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'paid_on'   => ['required', 'date'],
            'method'    => ['required', 'string', 'in:cash,check,bank,card,online,other'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes'     => ['nullable', 'string'],
        ]);

        $payment = $rentInvoice->payments()->create($data);

        $rentInvoice->recomputeStatus();

        // Email the landlord (if they opted in).
        $user = $rentInvoice->unit->property->user;
        if ($user) {
            $setting = Setting::for($user);
            if ($setting->notify_payment_received && $user->email) {
                Mail::to($user->email)->send(new PaymentReceivedMail($payment, $setting));
            }
        }

        return redirect()->route('rent-invoices.show', $rentInvoice)
            ->with('success', 'Payment of $' . number_format($payment->amount, 2) . ' recorded.');
    }

    public function show(Request $request, Payment $payment)
    {
        $this->authorizePayment($request, $payment);

        $payment->load('invoice.unit.property');

        return view('rent.payment-show', compact('payment'));
    }

    public function destroy(Request $request, Payment $payment)
    {
        $this->authorizePayment($request, $payment);

        $invoice = $payment->invoice;

        $payment->delete();

        $invoice->recomputeStatus();

        return redirect()->route('rent-invoices.show', $invoice)
            ->with('success', 'Payment removed.');
    }

    protected function authorizeInvoice(Request $request, RentInvoice $invoice): void
    {
        if ($invoice->unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    protected function authorizePayment(Request $request, Payment $payment): void
    {
        if ($payment->invoice->unit->property->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    /**
     * Download all payments as CSV.
     */
    public function exportCsv(Request $request)
    {
        $payments = Payment::query()
            ->whereHas('invoice.unit.property', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with(['invoice.unit.property', 'invoice.lease'])
            ->orderBy('paid_on')
            ->get();

        $csv = Writer::createFromString('');
        $csv->insertOne(['Date', 'Invoice', 'Tenant', 'Property', 'Unit', 'Amount', 'Method', 'Reference']);

        foreach ($payments as $p) {
            $csv->insertOne([
                $p->paid_on->format('Y-m-d'),
                $p->invoice->invoice_number,
                $p->invoice->lease?->tenant_name ?? '',
                $p->invoice->unit?->property?->name ?? '',
                $p->invoice->unit?->label ?? '',
                $p->amount,
                Payment::METHODS[$p->method] ?? $p->method,
                $p->reference ?? '',
            ]);
        }

        return response(stream_get_contents($csv->getContent()), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payments-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
