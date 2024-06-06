<?php

namespace App\Helpers;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberHelper
{
    const DEFAULT_AREA_FORMAT = 'ID';

    /**
     * @param string $value
     * @return string
     */
    public static function convert($value)
    {
        if (!$value) {
            return $value;
        }

        $firstChar = substr($value, 0, 1);
        
        if ($firstChar === '+') {
            return (new PhoneNumber($value))->formatE164();
        }

        // Default Indonesian Number
        return (new PhoneNumber($value, self::DEFAULT_AREA_FORMAT))->formatE164();
    }
}
