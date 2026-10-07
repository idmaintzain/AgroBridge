<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Delivered = 'delivered';
    case Disputed = 'disputed';
    case Released = 'released';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'Awaiting payment',
            self::Paid => 'Payment held',
            self::Delivered => 'Handover recorded',
            self::Disputed => 'Disputed',
            self::Released => 'Released to farmer',
            self::Completed => 'Paid out',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }
}
