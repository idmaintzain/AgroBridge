<?php

namespace App\Http\Controllers;

use App\Services\DemoBank;
use App\Services\WalletException;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class WalletController extends Controller
{
    public function fund(Request $request, DemoBank $bank)
    {
        $this->guard($request);

        try {
            $bank->fund($request->user(), $this->kobo($request));
        } catch (WalletException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Demonstration funds were added to Available.');
    }

    public function withdraw(Request $request, DemoBank $bank)
    {
        $this->guard($request);

        try {
            $bank->withdraw($request->user(), $this->kobo($request));
        } catch (WalletException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Demonstration funds were withdrawn from Available.');
    }

    public function transfer(Request $request, DemoBank $bank)
    {
        $this->guard($request);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:180'],
            'amount' => ['required', 'string'],
        ]);

        try {
            $bank->transfer($request->user(), $data['email'], $this->koboFrom($data['amount']));
        } catch (WalletException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Demonstration funds were sent to that AgroBridge account.');
    }

    private function guard(Request $request): void
    {
        abort_if($request->user()->is_admin, 403);
    }

    private function kobo(Request $request): int
    {
        $data = $request->validate([
            'amount' => ['required', 'string'],
        ]);

        return $this->koboFrom($data['amount']);
    }

    private function koboFrom(string $naira): int
    {
        try {
            return Money::toKobo($naira);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => $e->getMessage(),
            ]);
        }
    }
}
