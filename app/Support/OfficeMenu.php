<?php

namespace App\Support;

use App\Models\User;

class OfficeMenu
{
    /**
     * @return list<array{label: string, route: string, match: string}>
     */
    public static function for(User $user): array
    {
        if ($user->is_admin) {
            return [
                ['label' => 'Desk', 'route' => 'desk', 'match' => 'desk'],
                ['label' => 'People', 'route' => 'admin.people', 'match' => 'admin.people'],
                ['label' => 'All orders', 'route' => 'admin.orders', 'match' => 'admin.orders'],
                ['label' => 'Disputes', 'route' => 'admin.disputes', 'match' => 'admin.disputes'],
                ['label' => 'Fees and windows', 'route' => 'admin.settings', 'match' => 'admin.settings'],
            ];
        }

        $items = [
            ['label' => 'Desk', 'route' => 'desk', 'match' => 'desk'],
        ];

        if ($user->buys) {
            $items[] = ['label' => 'Market', 'route' => 'market', 'match' => 'market'];
            $items[] = ['label' => 'Purchases', 'route' => 'purchases', 'match' => 'purchases'];
        }

        if ($user->sells) {
            $items[] = ['label' => 'My produce', 'route' => 'office.listings', 'match' => 'office.listings'];
            $items[] = ['label' => 'List produce', 'route' => 'listings.create', 'match' => 'listings.create'];
            $items[] = ['label' => 'Sales', 'route' => 'sales', 'match' => 'sales'];
        }

        $items[] = ['label' => 'Wallet', 'route' => 'office.wallet', 'match' => 'office.wallet'];

        return $items;
    }
}
