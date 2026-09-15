<?php

namespace App\Services;

class ChangeCalculator
{
    /**
     * Indian currency denominations in paise, largest first.
     *
     * @var array<int, int>
     */
    protected $denominationsInPaise = [
        200000, 100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100, 50, 20, 10,
    ];

    /**
     * @param  float|int|string  $amount
     * @return array{amount: float, parts: array<int, string>, summary: string}
     */
    public function breakdown($amount)
    {
        $remaining = (int) round(((float) $amount) * 100);

        if ($remaining <= 0) {
            return [
                'amount' => 0.0,
                'parts' => [],
                'summary' => '₹0.00',
            ];
        }

        $parts = [];

        foreach ($this->denominationsInPaise as $value) {
            $count = (int) floor($remaining / $value);

            if ($count < 1) {
                continue;
            }

            $parts[] = $count.'×'.$this->label($value);
            $remaining -= $count * $value;
        }

        $rupees = round(((float) $amount), 2);

        return [
            'amount' => $rupees,
            'parts' => $parts,
            'summary' => '₹'.number_format($rupees, 2, '.', '').' → '.implode(' + ', $parts),
        ];
    }

    /**
     * @param  int  $paise
     * @return string
     */
    protected function label($paise)
    {
        if ($paise >= 100) {
            return (string) (int) ($paise / 100);
        }

        return number_format($paise / 100, 2, '.', '');
    }
}
