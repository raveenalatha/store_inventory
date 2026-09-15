<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | Products with a stock quantity below this value are treated as low stock.
    | Change LOW_STOCK_THRESHOLD in the .env file without touching application code.
    |
    */

    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 10),

];
