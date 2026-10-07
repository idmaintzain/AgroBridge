<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function naira(int $kobo): string
    {
        $sign = $kobo < 0 ? '-' : '';
        $kobo = abs($kobo);
        $whole = intdiv($kobo, 100);
        $fraction = str_pad((string) ($kobo % 100), 2, '0', STR_PAD_LEFT);

        return $sign.'₦'.number_format($whole).'.'.$fraction;
    }

    public static function toKobo(string $naira): int
    {
        $naira = trim($naira);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $naira)) {
            throw new InvalidArgumentException('Enter naira as a number with at most two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $naira, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }
}
