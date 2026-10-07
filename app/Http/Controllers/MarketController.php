<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Support\Nigeria;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $crop = $request->string('crop')->trim()->toString();
        $state = $request->string('state')->trim()->toString();

        $listings = Listing::query()
            ->with('farmer')
            ->where('is_active', true)
            ->whereRaw('quantity_on_hand > quantity_reserved')
            ->when($crop !== '', fn ($query) => $query->where('crop', $crop))
            ->when($state !== '', fn ($query) => $query->where('state', $state))
            ->orderBy('crop')
            ->paginate(24)
            ->withQueryString();

        return view('market.index', [
            'listings' => $listings,
            'crops' => Nigeria::crops(),
            'states' => Nigeria::states(),
            'crop' => $crop,
            'state' => $state,
        ]);
    }
}
