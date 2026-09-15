<?php

namespace Tests\Unit;

use App\Services\ChangeCalculator;
use PHPUnit\Framework\TestCase;

class ChangeCalculatorTest extends TestCase
{
    public function test_it_breaks_change_into_indian_denominations()
    {
        $result = (new ChangeCalculator())->breakdown(22.80);

        $this->assertSame(22.8, $result['amount']);
        $this->assertContains('1×20', $result['parts']);
        $this->assertContains('1×2', $result['parts']);
        $this->assertStringContainsString('₹22.80 →', $result['summary']);
    }

    public function test_zero_change()
    {
        $result = (new ChangeCalculator())->breakdown(0);

        $this->assertSame([], $result['parts']);
        $this->assertSame('₹0.00', $result['summary']);
    }
}
