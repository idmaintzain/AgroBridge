<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\LedgerEntry;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function desk(Request $request)
    {
        $user = $request->user();

        if ($user->is_admin) {
            return view('office.desk', [
                'role' => $user->roleLabel(),
                'cards' => [
                    ['label' => 'People', 'value' => (string) \App\Models\User::query()->count(), 'note' => 'Accounts on the platform'],
                    ['label' => 'Listings', 'value' => (string) Listing::query()->where('is_active', true)->count(), 'note' => 'Visible produce'],
                    ['label' => 'Open disputes', 'value' => (string) Order::query()->where('status', OrderStatus::Disputed)->count(), 'note' => 'Waiting for a ruling'],
                    ['label' => 'Payments held', 'value' => (string) Order::query()->where('status', OrderStatus::Paid)->count(), 'note' => 'Buyer has paid, handover not yet recorded'],
                ],
            ]);
        }

        $cards = [];

        if ($user->sells) {
            $cards[] = ['label' => 'Listings', 'value' => (string) $user->listings()->where('is_active', true)->count(), 'note' => 'Produce you currently offer'];
            $cards[] = ['label' => 'Sales awaiting a code', 'value' => (string) $user->sales()->where('status', OrderStatus::Paid)->count(), 'note' => 'The buyer has paid. Enter their collection code.'];
        }

        if ($user->buys) {
            $cards[] = ['label' => 'To pay', 'value' => (string) $user->purchases()->where('status', OrderStatus::AwaitingPayment)->count(), 'note' => 'Reserved quantity waiting for payment'];
            $cards[] = ['label' => 'To collect', 'value' => (string) $user->purchases()->where('status', OrderStatus::Paid)->count(), 'note' => 'Money is held. Confirm collection or read out the code.'];
        }

        $cards[] = ['label' => 'Available', 'value' => \App\Support\Money::naira((int) $user->wallet->available_kobo), 'note' => 'Ready to spend or withdraw in this demonstration'];

        return view('office.desk', [
            'role' => $user->roleLabel(),
            'cards' => $cards,
        ]);
    }

    public function listings(Request $request)
    {
        abort_unless($request->user()->sells, 403);

        $listings = Listing::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('office.listings', ['listings' => $listings]);
    }

    public function wallet(Request $request)
    {
        abort_if($request->user()->is_admin, 403);

        $entries = LedgerEntry::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(20);

        return view('office.wallet', [
            'entries' => $entries,
            'wallet' => $request->user()->wallet,
        ]);
    }
}
