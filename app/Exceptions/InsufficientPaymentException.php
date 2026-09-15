<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;

class InsufficientPaymentException extends Exception
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct($grandTotal)
    {
        parent::__construct('Amount given is less than the bill total.');

        $this->errors = [
            'amount_given' => ['Amount given must be at least ₹'.number_format((float) $grandTotal, 2, '.', '').'.'],
        ];
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return ApiResponse::error($this->getMessage(), $this->errors, 422);
    }
}
