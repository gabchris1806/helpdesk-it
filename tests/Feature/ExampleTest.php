<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Location::create(['name' => 'Kantor Pusat']);
        Category::create(['name' => 'Internet']);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
