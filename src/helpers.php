<?php

declare(strict_types=1);

use TeamChallengeApps\Distance\Distance;

if (!function_exists('distance_value')) {
    /**
     * Convert a value into a Distance object if not already.
     *
     * @param Distance|float|int $value
     * @param string $unit
     * @return Distance
     */
    function distance_value(Distance|float|int $value, string $unit = 'meters'): Distance
    {
        return $value instanceof Distance ? $value : new Distance($value, $unit);
    }
}

if (!function_exists('distance_get')) {
    /**
     * Convert a value to a specific unit.
     *
     * @param Distance|float|int $value
     * @param string $unit
     * @param string $from
     * @return float
     */
    function distance_get(Distance|float|int $value, string $unit = 'meters', string $from = 'meters'): float
    {
        $distance = $value instanceof Distance ? $value : new Distance($value, $from);
        return $distance->asUnit($unit);
    }
}
