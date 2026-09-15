<?php

namespace App\Services;

class OrderCalculator
{
    /**
     * Calculate line totals from the product's stored price and tax rate.
     *
     * subtotal = unit_price * quantity
     * tax      = subtotal * tax_rate / 100
     * total    = subtotal + tax
     *
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function lineTotals($unitPrice, $taxRate, $quantity)
    {
        $subtotal = $this->roundMoney(((float) $unitPrice) * ((int) $quantity));
        $taxAmount = $this->roundMoney($subtotal * ((float) $taxRate) / 100);
        $totalAmount = $this->roundMoney($subtotal + $taxAmount);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * @param  array<int, array{subtotal: float, tax_amount: float, total_amount: float}>  $lineTotals
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function orderTotals(array $lineTotals)
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($lineTotals as $line) {
            $subtotal += $line['subtotal'];
            $taxAmount += $line['tax_amount'];
        }

        $subtotal = $this->roundMoney($subtotal);
        $taxAmount = $this->roundMoney($taxAmount);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $this->roundMoney($subtotal + $taxAmount),
        ];
    }

    /**
     * @param  float|int|string  $amount
     * @return float
     */
    public function roundMoney($amount)
    {
        return round((float) $amount, 2);
    }
}
