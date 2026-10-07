<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_farmer_uploads_a_photograph_when_listing_produce(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->create(['sells' => true, 'buys' => false]);

        $this->actingAs($farmer)->post(route('listings.store'), $this->fields([
            'image' => UploadedFile::fake()->image('yam.jpg', 800, 600),
        ]))->assertRedirect();

        $listing = Listing::query()->first();
        $this->assertNotNull($listing->image_path);
        Storage::disk('public')->assertExists($listing->image_path);
        $this->actingAs($farmer)->get(route('listings.show', $listing))->assertSee('storage/'.$listing->image_path, false);
    }

    public function test_a_farmer_can_replace_and_remove_the_photograph(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->create(['sells' => true]);
        $this->actingAs($farmer)->post(route('listings.store'), $this->fields([
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]));

        $listing = Listing::query()->first();
        $first = $listing->image_path;

        $this->actingAs($farmer)->post(route('listings.update', $listing), $this->fields([
            '_method' => 'PUT',
            'image' => UploadedFile::fake()->image('second.png'),
            'is_active' => '1',
        ]))->assertRedirect();

        $listing->refresh();
        $this->assertNotSame($first, $listing->image_path);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($listing->image_path);

        $this->actingAs($farmer)->post(route('listings.update', $listing), $this->fields([
            '_method' => 'PUT',
            'remove_image' => '1',
            'is_active' => '1',
        ]))->assertRedirect();

        $this->assertNull($listing->fresh()->image_path);
    }

    public function test_a_new_listing_without_a_photograph_is_refused(): void
    {
        $farmer = User::factory()->create(['sells' => true]);

        $this->actingAs($farmer)->post(route('listings.store'), $this->fields())
            ->assertSessionHasErrors('image');
        $this->assertSame(0, Listing::query()->count());
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function fields(array $extra = []): array
    {
        return $extra + [
            'crop' => 'Yam',
            'unit' => 'crate',
            'quantity_on_hand' => 10,
            'price_naira' => '12000',
            'state' => 'Benue',
            'lga' => 'Otukpo',
            'collect_by' => now()->addWeek()->toDateString(),
            'description' => 'Crated yam.',
            'is_active' => '1',
        ];
    }
}
