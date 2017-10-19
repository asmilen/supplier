<?php

namespace Tests\Feature;

use Sentinel;
use Tests\TestCase;
use App\Events\SupplierUpserted;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SupplierTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory('App\Models\User')->create([
            'is_superadmin' => true,
        ]);
    }

    public function test_listing()
    {
        Sentinel::login($this->user);

        factory('App\Models\Supplier')->create([
            'name' => 'Test',
            'full_name' => 'Test',
            'code' => 'Test',
            'status' => true,
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'tax_number' => '111111111',
            'type' => 0,
            'sup_type' => 1,
            'price_active_time' => 10,
        ]);

        $this->getJson('/suppliers/listing')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Test',
                        'full_name' => 'Test',
                        'code' => 'Test',
                        'status' => true,
                        'phone' => '0912345678',
                        'email' => 'test@example.com',
                        'tax_number' => '111111111',
                        'type' => 0,
                        'sup_type' => 1,
                        'price_active_time' => 10,
                    ],
                ],
                'total_items' => 1,
                'all' => 1,
            ]);
    }

    public function test_admin_can_create_a_supplier()
    {
        Sentinel::login($this->user);

        $this->expectsEvents(SupplierUpserted::class);

        $this->postJson('/suppliers', [
            'name' => 'Test',
            'full_name' => 'Test',
            'code' => 'TEST',
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'tax_number' => '111111111',
            'type' => 0,
            'sup_type' => 1,
            'price_active_time' => 10,
            'status' => true,
            'province_id' => 1,
            'district_id' => 9,
            'address' => 'test address',
            'addressCode' => '89',
            'bank_account' => '424242424242',
            'bank_account_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test',
            'full_name' => 'Test',
            'code' => 'TEST',
            'phone' => '0912345678',
            'email' => 'test@example.com',
            'tax_number' => '111111111',
            'type' => 0,
            'sup_type' => 1,
            'price_active_time' => 240,
            'status' => true,
        ]);

        $this->assertDatabaseHas('supplier_addresses', [
            'province_id' => 1,
            'district_id' => 9,
            'address' => 'test address',
            'addressCode' => 89,
            'status' => true,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('supplier_bank_accounts', [
            'bank_account' => '424242424242',
            'bank_account_name' => 'John Doe',
            'status' => true,
            'is_default' => true,
        ]);
    }
}
