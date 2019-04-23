<?php

namespace SouthCoast\Helpers;

class Number
{
    const PI = M_PI;

    /**
     * Maps a value to a different range
     *
     * @param int|float $value
     * @param array $range_in
     * @param array $range_out
     * @return int|float
     */
    public static function rangeMap($value, $range_in, $range_out, $precision = 2)
    {
        ArrayHelper::walk($range_in, function (&$element, $precision) {
            $element = (float) number_format($element, $precision);
        }, $precision);

        ArrayHelper::walk($range_out, function (&$element, $precision) {
            $element = (float) number_format($element, $precision);
        }, $precision);

        $mapped = ((float) $value - $range_in[0]) / ($range_in[1] - $range_in[0]) * ($range_out[1] - $range_out[0]) + $range_out[0];

        return (float) number_format($mapped, $precision);
    }

    /**
     * @param $d
     */
    public function pi($d = 1): float
    {
        return Number::PI / $d;
    }

    /**
     * Checks if the value is in the provided range
     *
     * @param int|float $value      The value we want to check
     * @param array $range          The range in which the value should reside
     * @throws Exception            When there is no numeric value provided
     */
    public static function isInRange($value, array $range): bool
    {
        if (!is_numeric($value)) {
            throw new \Exception('The provided value is not a numeric value!', 1);
        }

        return ($value >= $range[0] && $value <= $range[1]) ? true : false;
    }

    /**
     * Checks if the provided value is numeric
     *
     * @param mixed $value
     * @return bool
     */
    public static function isNumber($value): bool
    {
        return Validate::isNumber($value);
    }

    /**
     * Checks if the provided value is an integer
     *
     * @param mixed $value
     * @return bool
     */
    public static function isInteger($value): bool
    {
        return Validate::isInteger($value);
    }

    /**
     * Checks if the provided value is a float
     *
     * @param mixed $value
     * @return bool
     */
    public static function isFloat($value): bool
    {
        return Validate::isFloat($value);
    }

    /**
     * Converts the provided value in a float
     *
     * @param mixed $value
     * @param int $precision
     * @return float
     */
    public static function convert2Float($value, int $precision = 2, $rounding_mode = PHP_ROUND_HALF_UP): float
    {
        if (!Validate::isNumber($value)) {
            return null;
        }

        return round((float) $value, $precision, $rounding_mode);
    }

    /**
     * Converts the provided value in a integer
     *
     * @param mixed $value
     * @param mixed $rounding_mode
     * @return int
     */
    public static function convert2Integer($value, $rounding_mode = PHP_ROUND_HALF_UP): int
    {
        if (!Validate::isNumber($value)) {
            return null;
        }

        return (int) round((float) $value, 0, $rounding_mode);
    }

    /**
     * Converts a numeric string into its float or integer value
     *
     * @param string $value         The to-be converted numeric string
     * @return float|int|null       returns a float or integer. Null if not numeric.
     */
    public static function string2Number(string $value)
    {
        if (!Validate::isNumber($value)) {
            return null;
        }

        if (Validate::isFloat($value)) {
            return Number::convert2Float($value);
        }

        if (Validate::isInteger($value)) {
            return Number::convert2Integer($value);
        }

        return null;
    }

    /**
     * Checks if the provided value is even
     *
     * @param int|bool $alpha
     * @return bool|null
     */
    private static function isEven($number): bool
    {
        if (!Validate::isNumber($number)) {
            return null;
        }

        return $number % 2 ? true : false;
    }
}
