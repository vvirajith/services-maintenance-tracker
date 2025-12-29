<?php

namespace App\Exceptions;

use Exception;

class AssetNotInStockException extends Exception
{
    protected $message = 'Asset must be IN_STOCK to be handed over to service center.';

    public function __construct(string $message = null, int $code = 422)
    {
        parent::__construct($message ?? $this->message, $code);
    }
}
