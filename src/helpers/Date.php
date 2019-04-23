<?php
namespace SouthCoast\Helpers;

use DateInterval;
use DatePeriod;
use DateTime;

class Date
{
    /**
     * @param string $begin             The beginning of the period
     * @param string $end               The end of the period
     * @param string $intervalString    The interval (1 day by default)
     * @return DatePeriod
     */
    public static function getPeriodBetween(string $begin, string $end, string $intervalString = '1 day'): DatePeriod
    {
        /* Create new DateTime Objects from the provided values */
        $begin = new DateTime($begin);
        $end = new DateTime($end);
        /* Modify the end date by adding a single day to it, yeah i know, strange php thing... */
        $end = $end->modify('+1 day');
        /* Create a new interval based on the provided interval string */
        $interval = DateInterval::createFromDateString($intervalString);
        /* Return a new date time period based on the interval */
        return new DatePeriod($begin, $interval, $end);
    }

    /**
     * @param string $start_date        The start date for the range
     * @param string $end_date          The end date for the range
     * @return array                    Comprised of the start and end dates for the weeks
     */
    public static function breakRangeIntoWeeks(string $start_date, string $end_date): array
    {
        /* Get the start dates for the weeks */
        $start_dates = self::getPeriodBetween($start_date, $end_date, '1 week');
        /* Create the response array */
        $response = [];
        /* Loop over all the dates */
        foreach ($start_dates as $date) {
            /* Set the key to the start date and the value to the end date */
            $response[$date->format('Y-m-d')] = $date->modify('+6 days')->format('Y-m-d');
        }
        /* Return the response */
        return $response;
    }

    /**
     * Checks if date A was earlier that date B
     *
     * @param $date_a
     * @param $date_b
     * @return bool     true if it is, false if it isn't :)
     */
    public static function wasEarlier($date_a, $date_b): bool
    {
        return strtotime($date_b) >= strtotime($date_a);
    }
}
