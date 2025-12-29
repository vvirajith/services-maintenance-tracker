<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected function executeInTransaction(callable $callback)
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function logAction(string $action, array $data = []): void
    {
        Log::info($action, $data);
    }
}
