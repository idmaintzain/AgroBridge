<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_level_sees_only_its_own_menu(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'sells' => false, 'buys' => false]);
        $farmer = User::factory()->create(['is_admin' => false, 'sells' => true, 'buys' => false]);
        $buyer = User::factory()->create(['is_admin' => false, 'sells' => false, 'buys' => true]);
        $both = User::factory()->create(['is_admin' => false, 'sells' => true, 'buys' => true]);

        $this->actingAs($admin)->get(route('desk'))
            ->assertOk()
            ->assertSee('People')
            ->assertSee('Disputes')
            ->assertSee('Fees and windows')
            ->assertDontSee('List produce')
            ->assertDontSee('Purchases');

        $this->actingAs($farmer)->get(route('desk'))
            ->assertOk()
            ->assertSee('My produce')
            ->assertSee('Sales')
            ->assertDontSee('Purchases')
            ->assertDontSee('Disputes');

        $this->actingAs($buyer)->get(route('desk'))
            ->assertOk()
            ->assertSee('Purchases')
            ->assertSee('Market')
            ->assertDontSee('My produce')
            ->assertDontSee('People');

        $this->actingAs($both)->get(route('desk'))
            ->assertOk()
            ->assertSee('Purchases')
            ->assertSee('Sales');

        $this->actingAs($buyer)->get(route('office.listings'))->assertForbidden();
        $this->actingAs($buyer)->get(route('admin.people'))->assertForbidden();
        $this->actingAs($admin)->get(route('office.wallet'))->assertForbidden();
    }
}
