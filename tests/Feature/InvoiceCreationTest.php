<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\TenantLease;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
    }

    public function test_create_invoice_page_explains_how_to_add_a_lease_when_none_exist(): void
    {
        $landlord = $this->createLandlord();

        $this->actingAs($landlord)
            ->get(route('rent-invoices.create'))
            ->assertOk()
            ->assertSee('No tenant leases are available to invoice yet.')
            ->assertSee(route('properties.create'))
            ->assertSee(route('leases.create'));
    }

    public function test_create_lease_page_explains_how_to_add_a_unit_when_none_exist(): void
    {
        $landlord = $this->createLandlord();

        $this->actingAs($landlord)
            ->get(route('leases.create'))
            ->assertOk()
            ->assertSee('No units are available for a lease yet.')
            ->assertSee(route('properties.create'))
            ->assertSee(route('properties.index'));
    }

    public function test_landlord_can_create_an_invoice_for_their_lease(): void
    {
        Mail::fake();

        $landlord = $this->createLandlord();
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
            'lease_start' => now()->startOfYear()->toDateString(),
            'monthly_rent' => 1250,
            'status' => TenantLease::STATUS_ACTIVE,
        ]);

        $this->actingAs($landlord)
            ->get(route('rent-invoices.create'))
            ->assertOk()
            ->assertSee('Taylor Tenant')
            ->assertSee('Unit 4');

        $this->actingAs($landlord)
            ->post(route('rent-invoices.store'), [
                'tenant_lease_id' => $lease->id,
                'billing_period_start' => now()->startOfMonth()->toDateString(),
                'billing_period_end' => now()->endOfMonth()->toDateString(),
                'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
                'invoice_total' => 1250,
                'advance_credit' => 250,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rent_invoices', [
            'tenant_lease_id' => $lease->id,
            'unit_id' => $unit->id,
            'invoice_total' => 1250,
            'advance_credit' => 250,
            'amount_due' => 1000,
        ]);

        $invoice = RentInvoice::firstOrFail();
        $this->actingAs($landlord)
            ->get(route('rent-invoices.show', $invoice))
            ->assertOk()
            ->assertSee('$1,250.00')
            ->assertSee('Advance credit')
            ->assertSee('$1,000.00');

        $this->actingAs($landlord)
            ->get(route('rent-invoices.index'))
            ->assertOk()
            ->assertSee('$1,250.00')
            ->assertSee('$250.00')
            ->assertSee('$1,000.00');

        $this->actingAs($landlord)
            ->put(route('rent-invoices.update', $invoice), [
                'billing_period_start' => now()->startOfMonth()->toDateString(),
                'billing_period_end' => now()->endOfMonth()->toDateString(),
                'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
                'invoice_total' => 1500,
                'advance_credit' => 300,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rent_invoices', [
            'id' => $invoice->id,
            'invoice_total' => 1500,
            'advance_credit' => 300,
            'amount_due' => 1200,
        ]);
    }

    public function test_advance_credit_cannot_exceed_invoice_amount(): void
    {
        $landlord = $this->createLandlord();
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
            'lease_start' => now()->startOfYear()->toDateString(),
            'monthly_rent' => 1250,
            'status' => TenantLease::STATUS_ACTIVE,
        ]);

        $this->actingAs($landlord)
            ->post(route('rent-invoices.store'), [
                'tenant_lease_id' => $lease->id,
                'billing_period_start' => now()->startOfMonth()->toDateString(),
                'billing_period_end' => now()->endOfMonth()->toDateString(),
                'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
                'invoice_total' => 1000,
                'advance_credit' => 1200,
            ])
            ->assertSessionHasErrors('advance_credit');

        $this->assertDatabaseCount('rent_invoices', 0);
    }

    private function createLandlord(): User
    {
        return User::create([
            'name' => 'Morgan Landlord',
            'email' => 'morgan@example.com',
            'password' => 'password',
        ]);
    }
}
