<?php

namespace SouthCoast\Helpers;

class Number
{
    /**
     * Maps a value to a different range
     *
     * @param int|float $value
     * @param array $range_in
     * @param array $range_out
     * @return int|float
     */
    public static function rangeMap($value, $range_in, $range_out, $precission = 2)
    {
        ArrayHelper::walk($range_in, function (&$element, $precission) {
            $element = (float) number_format($element, $precission);
        }, $precission);

        ArrayHelper::walk($range_out, function (&$element, $precission) {
            $element = (float) number_format($element, $precission);
        }, $precission);

        $mapped = ((float) $value - $range_in[0]) / ($range_in[1] - $range_in[0]) * ($range_out[1] - $range_out[0]) + $range_out[0];

        return (float) number_format($mapped, $precission);
    }

    /**
     * @param $d
     */
    public function pi($d = 1): float
    {
        return M_PI / $d;
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

}
