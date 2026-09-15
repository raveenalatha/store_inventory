<?php

namespace Tests\Unit;

use App\Support\AppDate;
use Tests\TestCase;

class AppDateTest extends TestCase
{
    public function test_it_formats_timestamps_in_india_timezone()
    {
        $this->assertSame('Asia/Kolkata', config('app.timezone'));
        $this->assertSame('14 Sep 2026, 11:12 AM', AppDate::display('2026-09-14 11:12:12'));
    }
}
