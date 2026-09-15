<?php

namespace Tests\Unit;

use App\Services\OrderCalculator;
use PHPUnit\Framework\TestCase;

class OrderCalculatorTest extends TestCase
{
    /**
     * @var OrderCalculator
     */
    protected $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new OrderCalculator();
    }

    public function test_it_calculates_line_and_order_totals()
    {
        $keyboard = $this->calculator->lineTotals(50, 10, 2);
        $mouse = $this->calculator->lineTotals(30, 5, 1);

        $this->assertSame(100.0, $keyboard['subtotal']);
        $this->assertSame(10.0, $keyboard['tax_amount']);
        $this->assertSame(110.0, $keyboard['total_amount']);

        $this->assertSame(30.0, $mouse['subtotal']);
        $this->assertSame(1.5, $mouse['tax_amount']);
        $this->assertSame(31.5, $mouse['total_amount']);

        $order = $this->calculator->orderTotals([$keyboard, $mouse]);

        $this->assertSame(130.0, $order['subtotal']);
        $this->assertSame(11.5, $order['tax_amount']);
        $this->assertSame(141.5, $order['total_amount']);
    }

    public function test_zero_tax_rate()
    {
        $line = $this->calculator->lineTotals(8.99, 0, 2);

        $this->assertSame(17.98, $line['subtotal']);
        $this->assertSame(0.0, $line['tax_amount']);
        $this->assertSame(17.98, $line['total_amount']);
    }
}
