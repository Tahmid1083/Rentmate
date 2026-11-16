<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Setting;
use App\Models\TenantLease;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReportAndReceiptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
    }

    public function test_reports_show_monthly_income_and_expenses_on_sqlite(): void
    {
        $landlord = User::create([
            'name' => 'Morgan Landlord',
            'email' => 'morgan@example.com',
            'password' => 'password',
        ]);
        $payment = $this->createPayment($landlord);

        Expense::create([
            'user_id' => $landlord->id,
            'property_id' => $payment->invoice->unit->property_id,
            'unit_id' => $payment->invoice->unit_id,
            'category' => 'repair',
            'incurred_on' => now()->toDateString(),
            'amount' => 200,
        ]);

        $this->actingAs($landlord)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('$1,250.00')
            ->assertSee('$200.00');
    }

    public function test_reports_render_for_a_landlord_with_no_properties(): void
    {
        $landlord = User::create([
            'name' => 'New Landlord',
            'email' => 'new@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($landlord)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('No expenses recorded');
    }

    public function test_landlord_can_print_their_payment_receipt_but_not_another_landlords(): void
    {
        $landlord = User::create([
            'name' => 'Morgan Landlord',
            'email' => 'morgan@example.com',
            'password' => 'password',
        ]);
        $payment = $this->createPayment($landlord);
        Setting::for($landlord)->update(['business_name' => 'Morgan Rentals']);

        $this->actingAs($landlord)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment receipt')
            ->assertSee('Morgan Rentals')
            ->assertSee('Taylor Tenant')
            ->assertSee('USD 1,250.00')
            ->assertSee('Print receipt');

        $otherLandlord = User::create([
            'name' => 'Other Landlord',
            'email' => 'other@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($otherLandlord)
            ->get(route('payments.receipt', $payment))
            ->assertForbidden();
    }

    private function createPayment(User $landlord): Payment
    {
        $property = Property::create([
            'user_id' => $landlord->id,
            'name' => 'Maple Apartments',
            'address_line_1' => '10 Maple Street',
            'city' => 'Springfield',
            'country' => 'US',
        ]);
        $unit = Unit::create([
            'property_id' => $property->id,
            'label' => 'Unit 4',
        ]);
        $lease = TenantLease::create([
            'unit_id' => $unit->id,
            'tenant_name' => 'Taylor Tenant',
            'tenant_email' => 'taylor@example.com',
            'lease_start' => now()->startOfYear()->toDateString(),
            'monthly_rent' => 1250,
            'status' => TenantLease::STATUS_ACTIVE,
        ]);
        $invoice = RentInvoice::create([
            'tenant_lease_id' => $lease->id,
            'unit_id' => $unit->id,
            'invoice_number' => 'INV-TEST-1',
            'billing_period_start' => now()->startOfMonth()->toDateString(),
            'billing_period_end' => now()->endOfMonth()->toDateString(),
            'due_date' => now()->endOfMonth()->toDateString(),
            'invoice_total' => 1250,
            'advance_credit' => 0,
            'amount_due' => 1250,
            'status' => RentInvoice::STATUS_PAID,
        ]);

        return $invoice->payments()->create([
            'amount' => 1250,
            'paid_on' => now()->toDateString(),
            'method' => 'bank',
            'reference' => 'TXN-123',
        ]);
    }
}
