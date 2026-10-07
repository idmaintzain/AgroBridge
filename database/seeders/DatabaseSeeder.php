<?php

namespace Database\Seeders;

use App\Enums\ProduceUnit;
use App\Models\LedgerEntry;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $farmer = $this->person('Iya Musa Adeyemi', 'farmer@agrobridge.test', sells: true, buys: false, kobo: 2_000_000);
        $buyer = $this->person('Chinedu Okonkwo', 'buyer@agrobridge.test', sells: false, buys: true, kobo: 50_000_000);
        $both = $this->person('Ada Okafor', 'ada@agrobridge.test', sells: true, buys: true, kobo: 15_000_000);
        $this->person('AgroBridge Admin', 'admin@agrobridge.test', sells: false, buys: false, kobo: 0, admin: true);

        $this->listing($farmer, 'Maize', ProduceUnit::Bag, 80, 1_850_000, 'Oyo', 'Iddo', 'Dried yellow maize from the Ibadan belt, bagged for collection at the farm gate.');
        $this->listing($farmer, 'Cassava', ProduceUnit::Kg, 500, 35_000, 'Ogun', 'Ijebu Ode', 'Fresh tubers. Buyer arranges sacks.');
        $this->listing($farmer, 'Tomato', ProduceUnit::Basket, 40, 800_000, 'Kaduna', 'Giwa', 'Ripe baskets. Collect within the date; they will not wait out a long dispute.');
        $this->listing($farmer, 'Rice', ProduceUnit::Bag, 60, 4_800_000, 'Kebbi', 'Argungu', 'Local parboiled rice, bagged. Collect at the mill yard.');
        $this->listing($farmer, 'Okra', ProduceUnit::Basket, 45, 350_000, 'Kwara', 'Ilorin East', 'Fresh pods. They should leave the farm the same day they are paid for.');
        $this->listing($both, 'Yam', ProduceUnit::Crate, 25, 1_200_000, 'Benue', 'Otukpo', 'Puna yam, crated.');
        $this->listing($both, 'Pepper', ProduceUnit::Basket, 30, 650_000, 'Kano', 'Dambatta', 'Fresh hot pepper. Short collection window is intentional.');
        $this->listing($both, 'Plantain', ProduceUnit::Crate, 36, 450_000, 'Edo', 'Ovia South-West', 'Mature plantain, crated for collection at the farm.');
        $this->listing($both, 'Groundnut', ProduceUnit::Bag, 50, 3_200_000, 'Kano', 'Bebeji', 'Unshelled groundnut, bagged and dry.');
        $this->listing($both, 'Onion', ProduceUnit::Bag, 40, 2_800_000, 'Sokoto', 'Wurno', 'Red onion, bagged. Keep the bags off wet ground.');
        $this->listing($both, 'Cocoa', ProduceUnit::Kg, 200, 650_000, 'Ondo', 'Idanre', 'Dried beans from split pods. Sold by the kilogram.');

        foreach ($this->catalogue() as [$seller, $crop, $unit, $quantity, $naira, $state, $lga, $note]) {
            $this->listing(
                $seller === 'ada' ? $both : $farmer,
                $crop,
                $unit,
                $quantity,
                $naira * 100,
                $state,
                $lga,
                $note,
            );
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: ProduceUnit, 3: int, 4: int, 5: string, 6: string, 7: string}>
     */
    private function catalogue(): array
    {
        return [
            ['farmer', 'Sorghum', ProduceUnit::Bag, 90, 18000, 'Kaduna', 'Zaria', 'Guinea corn, threshed and bagged.'],
            ['farmer', 'Millet', ProduceUnit::Bag, 70, 16500, 'Katsina', 'Funtua', 'Early millet, dry and bagged.'],
            ['farmer', 'Cowpea', ProduceUnit::Bag, 40, 42000, 'Borno', 'Biu', 'White cowpea with a black eye.'],
            ['farmer', 'Soybean', ProduceUnit::Bag, 55, 38000, 'Benue', 'Gboko', 'Clean soybean, bagged at the farm.'],
            ['farmer', 'Sesame', ProduceUnit::Bag, 30, 55000, 'Jigawa', 'Hadejia', 'White beniseed, dry.'],
            ['ada', 'Banana', ProduceUnit::Crate, 40, 6000, 'Ondo', 'Odigbo', 'Ripe bunches, crated for collection.'],
            ['ada', 'Orange', ProduceUnit::Crate, 35, 8500, 'Oyo', 'Ogbomosho North', 'Sweet oranges, crated.'],
            ['farmer', 'Pineapple', ProduceUnit::Crate, 28, 9000, 'Cross River', 'Akamkpa', 'Whole fruit with crowns still on.'],
            ['ada', 'Mango', ProduceUnit::Basket, 50, 4500, 'Benue', 'Makurdi', 'Ripe mangoes. Collect before they soften.'],
            ['farmer', 'Watermelon', ProduceUnit::Crate, 22, 7000, 'Kebbi', 'Jega', 'Whole melons, not cut.'],
            ['ada', 'Pawpaw', ProduceUnit::Crate, 30, 5500, 'Ogun', 'Remo North', 'Half-ripe pawpaw, crated.'],
            ['farmer', 'Coconut', ProduceUnit::Bag, 25, 12000, 'Lagos', 'Badagry', 'Mature nuts in the husk.'],
            ['ada', 'Ginger', ProduceUnit::Kg, 200, 1800, 'Kaduna', 'Kachia', 'Fresh rhizomes, sold by the kilogram.'],
            ['farmer', 'Garlic', ProduceUnit::Kg, 80, 2400, 'Plateau', 'Bokkos', 'Cured bulbs.'],
            ['ada', 'Sweet potato', ProduceUnit::Bag, 45, 14000, 'Nasarawa', 'Akwanga', 'Orange-fleshed, bagged.'],
            ['farmer', 'Potato', ProduceUnit::Bag, 35, 22000, 'Plateau', 'Barkin Ladi', 'Irish potato, bagged after harvest.'],
            ['ada', 'Cocoyam', ProduceUnit::Basket, 40, 8000, 'Anambra', 'Aguata', 'Whole corms, not peeled.'],
            ['farmer', 'Garden egg', ProduceUnit::Basket, 55, 3200, 'Enugu', 'Udi', 'White and green garden eggs.'],
            ['ada', 'Cucumber', ProduceUnit::Crate, 30, 6500, 'Plateau', 'Jos South', 'Field cucumber, crated.'],
            ['farmer', 'Carrot', ProduceUnit::Bag, 28, 15000, 'Plateau', 'Riyom', 'Washed carrots, bagged.'],
            ['ada', 'Cabbage', ProduceUnit::Crate, 24, 7500, 'Plateau', 'Jos North', 'Firm heads, outer leaves on.'],
            ['farmer', 'Ugwu', ProduceUnit::Basket, 80, 1500, 'Imo', 'Owerri West', 'Fluted pumpkin leaves, bundled.'],
            ['ada', 'Bitter leaf', ProduceUnit::Basket, 60, 2000, 'Anambra', 'Njikoka', 'Fresh leaves, not washed in the market.'],
            ['farmer', 'Waterleaf', ProduceUnit::Basket, 70, 1200, 'Rivers', 'Etche', 'Soft leaves for the same-day pot.'],
            ['ada', 'Egusi', ProduceUnit::Bag, 20, 48000, 'Kwara', 'Offa', 'Melon seed, dry and bagged.'],
            ['farmer', 'Cashew', ProduceUnit::Bag, 25, 36000, 'Kogi', 'Dekina', 'Raw nuts in shell.'],
            ['ada', 'Kolanut', ProduceUnit::Basket, 15, 9500, 'Ogun', 'Ijebu North', 'Red and white nuts.'],
            ['farmer', 'Shea', ProduceUnit::Bag, 30, 28000, 'Niger', 'Bida', 'Dried shea nuts.'],
            ['ada', 'Honey', ProduceUnit::Kg, 40, 4500, 'Kaduna', 'Kauru', 'Strained honey, sold by the kilogram.'],
            ['farmer', 'Catfish', ProduceUnit::Kg, 120, 2800, 'Oyo', 'Lagelu', 'Live catfish, weighed at collection.'],
            ['ada', 'Chicken', ProduceUnit::Kg, 50, 3500, 'Ogun', 'Obafemi Owode', 'Live broilers, sold by weight.'],
            ['farmer', 'Snail', ProduceUnit::Basket, 18, 12000, 'Edo', 'Ovia North-East', 'Live giant snails in a basket.'],
            ['ada', 'Mushroom', ProduceUnit::Kg, 40, 2200, 'Oyo', 'Akinyele', 'Oyster mushrooms, picked that morning.'],
            ['farmer', 'Sugarcane', ProduceUnit::Crate, 60, 3000, 'Niger', 'Mokwa', 'Cut stalks, tied for collection.'],
            ['ada', 'Cotton', ProduceUnit::Bag, 25, 32000, 'Zamfara', 'Gusau', 'Seed cotton, bagged dry.'],
            ['farmer', 'Tiger nut', ProduceUnit::Bag, 35, 18000, 'Niger', 'Kontagora', 'Dried tiger nut, bagged.'],
            ['ada', 'Avocado', ProduceUnit::Crate, 20, 11000, 'Cross River', 'Ikom', 'Hard-ripe pears, crated.'],
            ['farmer', 'Lime', ProduceUnit::Basket, 40, 3800, 'Ogun', 'Abeokuta South', 'Green limes, not juiced.'],
            ['ada', 'Pumpkin', ProduceUnit::Crate, 30, 5000, 'Nasarawa', 'Karu', 'Whole pumpkins.'],
            ['farmer', 'Zobo', ProduceUnit::Kg, 90, 1600, 'Kano', 'Bunkure', 'Dried hibiscus calyces.'],
            ['ada', 'Fonio', ProduceUnit::Bag, 18, 40000, 'Plateau', 'Langtang', 'Acha grain, cleaned and bagged.'],
            ['farmer', 'Locust bean', ProduceUnit::Basket, 22, 8500, 'Kwara', 'Kaiama', 'Fermented locust bean.'],
            ['ada', 'Oloyin', ProduceUnit::Bag, 35, 45000, 'Ekiti', 'Ado Ekiti', 'Honey beans, bagged.'],
            ['farmer', 'Garri', ProduceUnit::Bag, 70, 22000, 'Ogun', 'Ewekoro', 'White cassava garri, dry.'],
            ['ada', 'Palm oil', ProduceUnit::Kg, 150, 1900, 'Delta', 'Ethiope West', 'Red oil, sold by the kilogram.'],
            ['farmer', 'Atarodo', ProduceUnit::Basket, 35, 7000, 'Lagos', 'Ikorodu', 'Fresh scotch bonnet, baskets only.'],
            ['ada', 'Date', ProduceUnit::Crate, 12, 15000, 'Kano', 'Dawakin Tofa', 'Dried dates, crated.'],
            ['farmer', 'Guava', ProduceUnit::Basket, 40, 3000, 'Oyo', 'Egbeda', 'Firm fruit, a few turning yellow.'],
            ['ada', 'Soursop', ProduceUnit::Crate, 16, 8000, 'Osun', 'Ife Central', 'Whole fruit, spines intact.'],
            ['farmer', 'Turmeric', ProduceUnit::Kg, 100, 1500, 'Kaduna', 'Jaba', 'Fresh rhizomes. One cut piece shows the colour.'],
        ];
    }

    private function person(string $name, string $email, bool $sells, bool $buys, int $kobo, bool $admin = false): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'sells' => $sells,
            'buys' => $buys,
            'is_admin' => $admin,
        ]);

        $user->wallet->update(['available_kobo' => $kobo]);

        if ($kobo > 0) {
            LedgerEntry::query()->create([
                'order_id' => null,
                'user_id' => $user->id,
                'account' => 'wallet',
                'direction' => 'credit',
                'type' => 'topup',
                'amount_kobo' => $kobo,
                'memo' => 'Opening demonstration balance',
                'created_at' => now(),
            ]);
        }

        return $user;
    }

    private function listing(User $farmer, string $crop, ProduceUnit $unit, int $quantity, int $priceKobo, string $state, string $lga, string $description): void
    {
        Listing::query()->create([
            'user_id' => $farmer->id,
            'crop' => $crop,
            'unit' => $unit,
            'quantity_on_hand' => $quantity,
            'quantity_reserved' => 0,
            'price_per_unit_kobo' => $priceKobo,
            'state' => $state,
            'lga' => $lga,
            'collect_by' => now()->addDays(10)->toDateString(),
            'description' => $description,
            'is_active' => true,
        ]);
    }
}
