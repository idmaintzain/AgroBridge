<?php

namespace App\Http\Controllers;

use App\Enums\ProduceUnit;
use App\Models\Listing;
use App\Services\EscrowException;
use App\Services\Settings;
use App\Support\Money;
use App\Support\Nigeria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function show(Listing $listing, Settings $settings)
    {
        $listing->load('farmer');
        $quote = null;

        if ($listing->availableQuantity() > 0) {
            $quote = $settings->quote($listing->price_per_unit_kobo);
        }

        return view('listings.show', [
            'listing' => $listing,
            'quote' => $quote,
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->sells, 403);

        return view('listings.form', $this->formData(new Listing([
            'crop' => 'Maize',
            'unit' => ProduceUnit::Bag,
            'quantity_on_hand' => 1,
            'is_active' => true,
            'collect_by' => now()->addDays(7)->toDateString(),
        ])));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->sells, 403);
        $data = $this->validateListing($request);
        $data['image_path'] = $this->storeImage($request);
        $data['quantity_reserved'] = 0;

        $listing = $request->user()->listings()->create($data);

        return redirect()->route('listings.show', $listing)->with('status', 'Your produce is on the market.');
    }

    public function edit(Listing $listing)
    {
        abort_unless(auth()->id() === $listing->user_id, 403);

        return view('listings.form', $this->formData($listing));
    }

    public function update(Request $request, Listing $listing)
    {
        abort_unless($request->user()->id === $listing->user_id, 403);
        $data = $this->validateListing($request, $listing);
        $data = $this->applyImageChange($request, $listing, $data);

        try {
            $listing->update($data);
        } catch (EscrowException|\RuntimeException $e) {
            return back()->withInput()->with('error', 'Quantity on hand cannot drop below what buyers have already reserved.');
        }

        return redirect()->route('listings.show', $listing)->with('status', 'Listing updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Listing $listing): array
    {
        return [
            'listing' => $listing,
            'crops' => Nigeria::crops(),
            'states' => Nigeria::states(),
            'units' => ProduceUnit::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateListing(Request $request, ?Listing $listing = null): array
    {
        $data = $request->validate([
            'crop' => ['required', 'string', 'max:80'],
            'unit' => ['required', Rule::in(ProduceUnit::values())],
            'quantity_on_hand' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price_naira' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'state' => ['required', Rule::in(Nigeria::states())],
            'lga' => ['required', 'string', 'max:80'],
            'collect_by' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$listing ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $kobo = Money::toKobo($data['price_naira']);

        if ($kobo < 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'price_naira' => 'Price must be at least ₦1.00 per unit.',
            ]);
        }

        if ($listing && (int) $data['quantity_on_hand'] < $listing->quantity_reserved) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity_on_hand' => 'Quantity on hand cannot drop below the reserved quantity of '.$listing->quantity_reserved.'.',
            ]);
        }

        return [
            'crop' => $data['crop'],
            'unit' => $data['unit'],
            'quantity_on_hand' => (int) $data['quantity_on_hand'],
            'price_per_unit_kobo' => $kobo,
            'state' => $data['state'],
            'lga' => $data['lga'],
            'collect_by' => $data['collect_by'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyImageChange(Request $request, Listing $listing, array $data): array
    {
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($listing->image_path);
            $data['image_path'] = $this->storeImage($request);

            return $data;
        }

        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($listing->image_path);
            $data['image_path'] = null;
        }

        return $data;
    }

    private function storeImage(Request $request): string
    {
        return $request->file('image')->store('listings', 'public');
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
