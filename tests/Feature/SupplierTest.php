<?php

namespace Tests\Feature;

use Sentinel;
use Tests\TestCase;
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
            'tax_number' => '111111111',
            'type' => 1,
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
                        'tax_number' => '111111111',
                        'type' => 1,
                    ],
                ],
                'total_items' => 1,
                'all' => 1,
            ]);
    }
}
