<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends Exception
{
    /**
     * @var array
     */
    protected $errors;

    public function __construct(array $errors = [])
    {
        parent::__construct('Insufficient stock');

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Render the exception as a consistent JSON API response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        return ApiResponse::error($this->getMessage(), $this->errors, 422);
    }
}
