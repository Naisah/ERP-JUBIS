<?php

namespace App\Support;

final class Money
{
    public static function cents(string|int|float|null $amount): int
    {
        $value = is_float($amount) ? number_format($amount, 2, '.', '') : (string) ($amount ?? 0);
        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $value, $parts)) {
            throw new \InvalidArgumentException('Money must contain at most two decimal places.');
        }

        $cents = ((int) $parts[2] * 100) + (int) str_pad($parts[3] ?? '', 2, '0');

        return ($parts[1] ?? '') === '-' ? -$cents : $cents;
    }

    public static function decimal(int $cents): string
    {
        return ($cents < 0 ? '-' : '').intdiv(abs($cents), 100).'.'.str_pad((string) (abs($cents) % 100), 2, '0', STR_PAD_LEFT);
    }
}
