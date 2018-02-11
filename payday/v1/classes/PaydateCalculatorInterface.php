<?php

interface PaydateCalculatorInterface
{

    /**
     * This function takes a paydate model and a first paydate and generates the next $number_of_paydates paydates.
     *
     * @param string $paydateModel The paydate model, one of the items in the spec
     * @param string $paydateOne First paydate as a string in Y-m-d format, different from the second
     * @param int $numberOfPaydates The number of paydates to generate
     *
     * @return array the next paydates (from today) as strings in Y-m-d format
     */
    public function calculateNextPaydates(string $paydateModel, string $paydateOne, int $numberOfPaydates): array;

    /**
     * This function determines whether a given date in Y-m-d format is a holiday.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on a holiday
     */
    public function isHoliday(string $date): bool;

    /**
     * This function determines whether a given date in Y-m-d format is on a weekend.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on a weekend
     */
    public function isWeekend(string $date): bool;

    /**
     * This function determines whether a given date in Y-m-d format is on the same the program is ran.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on the day the program is ran
     */
    public function isToday(string $date): bool;

    /**
     * This function determines whether a given date in Y-m-d format is a valid paydate according to specification rules.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return int whether or not the given date is a valid
     */
    public function isValidPaydate(string $date): bool;

    /**
     * This function increases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to increment
     * @param string $unit adjustment unit
     *
     * @return string the calculated day's date as a string in Y-m-d format
     */
    public function increaseDate(string $date, int $count, string $unit = 'days'): string;

    /**
     * This function decreases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to decrement
     * @param string $unit adjustment unit
     *
     * @return string the calculated day's date as a string in Y-m-d format
     */
    public function decreaseDate(string $date, int $count, string $unit = 'days'): string;


}
