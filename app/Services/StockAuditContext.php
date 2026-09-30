<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Str;

class StockAuditContext
{
    private static $context = [];
    private static $requestGroupUuid;

    public static function run(array $context, Closure $callback)
    {
        $previous = self::$context;
        self::$context = array_merge($previous, $context);

        try {
            return $callback();
        } finally {
            self::$context = $previous;
        }
    }

    public static function current(): array
    {
        return self::$context;
    }

    public static function requestGroupUuid(): string
    {
        if (!self::$requestGroupUuid) {
            self::$requestGroupUuid = (string) Str::uuid();
        }

        return self::$requestGroupUuid;
    }
}
