<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UnitDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh');
    }

    public function test_landlord_can_delete_a_unit_from_unit_lists(): void
    {
        $landlord = User::create([
            'name' => 'Morgan Landlord',
            'email' => 'morgan@example.com',
            'password' => 'password',
        ]);
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

        $this->actingAs($landlord)
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee(route('units.destroy', $unit));

        $this->actingAs($landlord)
            ->get(route('properties.units.index', $property))
            ->assertOk()
            ->assertSee(route('units.destroy', $unit));

        $this->actingAs($landlord)
            ->delete(route('units.destroy', $unit))
            ->assertRedirect(route('properties.show', $property));

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
    }
}
