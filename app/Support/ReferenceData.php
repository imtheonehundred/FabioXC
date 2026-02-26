<?php

namespace App\Support;

class ReferenceData
{
    protected static ?array $countries = null;
    protected static ?array $timezones = null;
    protected static ?array $macTypes = null;
    protected static ?array $errorCodes = null;

    public static function countries(): array
    {
        if (self::$countries === null) {
            self::$countries = require base_path('resources/data/countries.php');
        }
        return self::$countries;
    }

    public static function timezones(): array
    {
        if (self::$timezones === null) {
            self::$timezones = require base_path('resources/data/timezones.php');
        }
        return self::$timezones;
    }

    public static function macTypes(): array
    {
        if (self::$macTypes === null) {
            self::$macTypes = require base_path('resources/data/mac_types.php');
        }
        return self::$macTypes;
    }

    public static function errorCodes(): array
    {
        if (self::$errorCodes === null) {
            self::$errorCodes = require base_path('resources/data/error_codes.php');
        }
        return self::$errorCodes;
    }

    public static function errorMessage(int $code): string
    {
        $codes = self::errorCodes();
        return $codes[$code] ?? 'Unknown error';
    }
}
