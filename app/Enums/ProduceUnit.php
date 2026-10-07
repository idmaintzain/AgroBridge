<?php

namespace App\Enums;

enum ProduceUnit: string
{
    case Kg = 'kg';
    case Bag = 'bag';
    case Basket = 'basket';
    case Crate = 'crate';

    public function label(): string
    {
        return match ($this) {
            self::Kg => 'kg',
            self::Bag => 'bag',
            self::Basket => 'basket',
            self::Crate => 'crate',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
