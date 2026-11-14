<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Setting;
use App\Models\TenantLease;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::create([
            'name'              => 'Demo Landlord',
            'email'             => 'demo@rental.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Setting::for($landlord);

        $property = Property::create([
            'user_id'        => $landlord->id,
            'name'           => 'Maple Court Apartments',
            'address_line_1' => '412 Maple Street',
            'city'           => 'Springfield',
            'state'          => 'IL',
            'postal_code'    => '62704',
            'country'        => 'US',
            'type'           => 'residential',
        ]);

        $unitA = Unit::create([
            'property_id'  => $property->id,
            'label'        => 'Apt 1A',
            'floor'        => 'Ground',
            'bedrooms'     => 2,
            'bathrooms'    => 1,
            'square_feet'  => 850,
            'market_rent'  => 1200,
            'is_occupied'  => true,
        ]);

        $unitB = Unit::create([
            'property_id'  => $property->id,
            'label'        => 'Apt 2B',
            'floor'        => 'Second',
            'bedrooms'     => 1,
            'bathrooms'    => 1,
            'square_feet'  => 600,
            'market_rent'  => 950,
            'is_occupied'  => false,
        ]);

        $lease = TenantLease::create([
            'unit_id'      => $unitA->id,
            'tenant_name'  => 'Jordan Rivera',
            'tenant_email' => 'jordan@example.com',
            'tenant_phone' => '555-0102',
            'lease_start'  => now()->startOfYear(),
            'lease_end'    => now()->startOfYear()->addYear(),
            'monthly_rent' => 1200,
            'deposit'      => 1200,
            'status'       => TenantLease::STATUS_ACTIVE,
        ]);

        $invoice = RentInvoice::create([
            'tenant_lease_id'      => $lease->id,
            'unit_id'              => $unitA->id,
            'invoice_number'       => 'INV-' . now()->year . '-' . strtoupper(Str::random(8)),
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end'   => now()->endOfMonth(),
            'due_date'             => now()->startOfMonth()->addDays(5),
            'amount_due'           => 1200,
            'status'               => RentInvoice::STATUS_OPEN,
        ]);

        $invoice->payments()->create([
            'amount'    => 600,
            'paid_on'   => now()->startOfMonth()->addDays(2),
            'method'    => 'bank',
            'reference' => 'ACH-001',
        ]);

        $invoice->recomputeStatus();

        MaintenanceRequest::create([
            'property_id' => $property->id,
            'unit_id'     => $unitA->id,
            'reporter_id' => $landlord->id,
            'title'       => 'Kitchen sink leaking',
            'description' => 'Tenant reported water pooling under the cabinet.',
            'category'    => MaintenanceRequest::CATEGORY_PLUMBING,
            'priority'    => MaintenanceRequest::PRIORITY_HIGH,
            'status'      => MaintenanceRequest::STATUS_OPEN,
        ]);

        Expense::create([
            'user_id'      => $landlord->id,
            'property_id'  => $property->id,
            'category'     => Expense::CATEGORY_MORTGAGE,
            'vendor'       => 'First National Bank',
            'description'  => 'Monthly mortgage payment',
            'incurred_on'  => now()->startOfMonth(),
            'amount'       => 850,
            'is_recurring' => true,
        ]);

        Expense::create([
            'user_id'      => $landlord->id,
            'property_id'  => $property->id,
            'unit_id'      => $unitA->id,
            'category'     => Expense::CATEGORY_REPAIR,
            'vendor'       => "Joe's Plumbing",
            'description'  => 'Emergency call-out — kitchen sink',
            'incurred_on'  => now()->subDays(3),
            'amount'       => 180,
            'is_recurring' => false,
        ]);

        Expense::create([
            'user_id'      => $landlord->id,
            'property_id'  => $property->id,
            'category'     => Expense::CATEGORY_INSURANCE,
            'vendor'       => 'State Farm',
            'description'  => 'Property insurance premium',
            'incurred_on'  => now()->startOfMonth()->addDays(1),
            'amount'       => 220,
            'is_recurring' => true,
        ]);

        Document::create([
            'user_id'     => $landlord->id,
            'name'        => 'lease-1A-template.pdf',
            'disk'        => 'private',
            'path'        => 'documents/lease-1A-template.pdf',
            'mime_type'   => 'application/pdf',
            'size'        => 0,
            'description' => 'Reference template — replace with the actual signed lease.',
        ]);
    }
}
