<?php
namespace Mdaliyan;

use Mdaliyan\Exceptions\InvalidDateException;
use InvalidArgumentException;


/**
 * Class Carbon
 * @package Mdaliyan\Carbon
 * @property      int $jYear
 * @property      int $jMonth
 * @property      int $jDay
 * @property      int jYearIso
 * @property      int jHour
 * @property      int jMinute
 * @property      int jSecond
 * @property      int jMicro
 * @property      int jDayOfWeek
 * @property      int jDayOfYear
 * @property      int jWeekOfYear
 * @property      int jDaysInMonth
 * @property      int jTimestamp
 * @property      int jWeekOfMonth
 * @property      int jAge
 * @property      int jQuarter
 * @property      int jOffset
 * @property      int jOffsetHours
 * @property      int jDst
 */
trait Jalali
{
    protected static $jWeekendDays = [self::FRIDAY];

    protected static $jWeekEndsAt = self::FRIDAY;

    protected static $jWeekStartsAt = self::SATURDAY;

    // Todo: if true, always return farsi numbers in jFormat just an idea.
    protected static $autoConvertDigits = false;

    /**
     * @var array
     */
    protected $formats = [
        'datetime' => '%Y-%m-%d %H:%M:%S',
        'date' => '%Y-%m-%d',
        'time' => '%H:%M:%S',
    ];

    private static $months = array(
        'farvardin' => 1,
        'ordibehesht' => 2,
        'khordad' => 3,
        'tir' => 4,
        'mordad' => 5,
        'shahrivar' => 6,
        'mehr' => 7,
        'aban' => 8,
        'azar' => 9,
        'dey' => 10,
        'bahman' => 11,
        'esfand' => 12,
        'فروردین' => 1,
        'اردیبهشت' => 2,
        'خرداد' => 3,
        'تیر' => 4,
        'مرداد' => 5,
        'شهریور' => 6,
        'مهر' => 7,
        'آبان' => 8,
        'آذر' => 9,
        'دی' => 10,
        'بهمن' => 11,
        'اسفند' => 12,
    );

    /**
     * @param $format
     * @return bool|string
     */
    public function jFormat($format)
    {
        // convert alias string
        if (in_array(strtolower($format), array_keys($this->formats))) {
            $format = $this->formats[$format];
        }

        return static::strftime($format, $this->getTimestamp(), $this->getTimezone());
    }

    ///////////////////////////////////////////////////////////////////
    //////////////////////////// CONSTRUCTORS /////////////////////////
    ///////////////////////////////////////////////////////////////////

    // TODO: Add Carbon::jParse() { Creates Carbon instance from string }
    /**
     * Create a carbon instance from a string.
     * This is an alias for the constructor that allows better fluent syntax
     * as it allows you to do Carbon::parse('Monday next week')->fn() rather
     * than (new Carbon('Monday next week'))->fn().
     * @param string|null $time
     * @param \DateTimeZone|string|null $tz
     * @return static
     */
//    public static function jParse($time = null, $tz = null){}

    // TODO: Add the Maximum Date Jalali can handle
//    public static function jMaxValue()

    // TODO: Add the Minimum Date Jalali can handle
//    public static function jMinValue()


    /**
     * Create a new jDate instance from a specific Jalali date and time.
     * If any of $year, $month or $day are set to null their now() values will
     * be used.
     * If $hour is null it will be set to its now() value and the default
     * values for $minute and $second will be their now() values.
     * If no params are passed, now() values will be returned.
     * @param null $year
     * @param null $month
     * @param null $day
     * @param null $hour
     * @param null $minute
     * @param null $second
     * @param null $tz
     * @return Carbon
     */
    public static function jCreate(
        $year = null,
        $month = null,
        $day = null,
        $hour = null,
        $minute = null,
        $second = null,
        $tz = null
    ) {
        static::jNowIfNull($year, $month, $day, $tz);

        if ($year < 0) {
            throw new InvalidArgumentException('Invalid Year Number');
        }
        if ($month < 0) {
            throw new InvalidArgumentException('Invalid Month Number');
        }
        if ($day < 0) {
            throw new InvalidArgumentException('Invalid Day Number');
        }
        if ($hour < 0) {
            throw new InvalidArgumentException('Invalid Hour Number');
        }
        if ($minute < 0) {
            throw new InvalidArgumentException('Invalid Minute Number');
        }
        if ($second < 0) {
            throw new InvalidArgumentException('Invalid Second Number');
        }
        static::fixWraps($year, $month, $day, $hour, $minute, $second);

        $G = static::toGregorian($year, $month, $day);

        return static::create($G[0], $G[1], $G[2], $hour, $minute, $second, $tz);
    }

    /**
     * @param null $year
     * @param null $month
     * @param null $day
     * @param null $hour
     * @param null $minute
     * @param null $second
     * @param null $tz
     * @return Carbon
     */
    public static function jCreateSafe(
        $year = null,
        $month = null,
        $day = null,
        $hour = null,
        $minute = null,
        $second = null,
        $tz = null
    ) {
        $fields = [
            'year' => [0, 1878],
            'month' => [0, 12],
            'day' => [0, 31],
            'hour' => [0, 24],
            'minute' => [0, 59],
            'second' => [0, 59],
        ];

        foreach ($fields as $field => $range) {
            if ($$field !== null && (!is_int($$field) || $$field < $range[0] || $$field > $range[1])) {
                throw new InvalidDateException($field, $$field);
            }
        }

        return static::jCreate($year, $month, $day, $hour, $minute, $second, $tz);
    }

    /**
     * @param null $year
     * @param null $month
     * @param null $day
     * @param null $tz
     * @return Carbon
     */
    public static function jCreateFromDate(
        $year = null,
        $month = null,
        $day = null,
        $tz = null
    ) {
        return static::jCreate($year, $month, $day, null, null, null, $tz);
    }

    /**
     * Create a Carbon instance from just a time. The date portion is set to today.
     * @param int|null $hour
     * @param int|null $minute
     * @param int|null $second
     * @param \DateTimeZone|string|null $tz
     * @return static
     */
    public static function jCreateFromTime(
        $hour = null,
        $minute = null,
        $second = null,
        $tz = null
    ) {
        return static::jCreate(null, null, null, $hour, $minute, $second, $tz);
    }

    /**
     * @param $format
     * @param $time
     * @param null $tz
     * @return Carbon
     */
    public static function jCreateFromFormat($format, $time, $tz = null)
    {
        $pd = static::parseFromFormat($format, $time);

        return static::jCreate($pd['year'], $pd['month'], $pd['day'], $pd['hour'],
            $pd['minute'], $pd['second'], $tz);
    }

    /**
     * Create a Carbon instance from a timestamp.
     * @param int $timestamp
     * @param \DateTimeZone|string|null $tz
     * @return static
     */
    public static function jCreateFromTimestamp($timestamp, $tz = null)
    {
        return static::createFromTimestamp($timestamp, $tz);
    }

    /**
     * Create a Carbon instance from an UTC timestamp.
     * @param int $timestamp
     * @return static
     */
    public static function jCreateFromTimestampUTC($timestamp)
    {
        return static::createFromTimestampUTC($timestamp);
    }

    protected function __jGet($name)
    {
        switch ($name) {
            case 'jDaysInMonth':
                return static::jDaysInMonth($this->jYear, $this->jMonth);
                break;

            case array_key_exists($name, $formats = [
                'jYear' => 'Y',
                'jYearIso' => 'o',
                'jMonth' => 'n',
                'jDay' => 'j',
                'jHour' => 'G',
                'jMinute' => 'i',
                'jSecond' => 's',
                'jMicro' => 'u',
                'jDayOfWeek' => 'w',
                'jDayOfYear' => 'z',
                'jWeekOfYear' => 'W',
                'jTimestamp' => 'U',
            ]):
                return (int)$this->jFormat($formats[$name]);

            case $name === 'jWeekOfMonth':
                return (int)ceil($this->jDay / static::DAYS_PER_WEEK);

            case $name === 'jAge':
                return (int)$this->diffInYears();

            case $name === 'jQuarter':
                return (int)ceil($this->jMonth / static::MONTHS_PER_QUARTER);

            case $name === 'jOffset':
                return $this->getOffset();

            case $name === 'jOffsetHours':
                return $this->offsetHours;

            case $name === 'jDst':
                // Todo: Whether or not the date is in daylight saving time ????
                return $this->jFormat('I') === '1';

            default:
                return false;
        }
    }

    public function __jSet($name, $value)
    {
        switch ($name) {
            case 'jYear':
                $this->jSetDate($value, $this->jMonth, $this->jDay);
                break;

            case 'jMonth':
                $this->jSetDate($this->jMonth, $value, $this->jDay);
                break;

            case 'jDay':
                $this->jSetDate($this->jMonth, $this->jMonth, $value);
                break;
        }
    }

    /**
     * Set the instance's year
     * @param int $value
     * @return static
     */
    public function jYear($value)
    {
        $this->jYear = $value;

        return $this;
    }

    /**
     * Set the instance's month
     * @param int $value
     * @return static
     */
    public function jMonth($value)
    {
        $this->jMonth = $value;

        return $this;
    }

    /**
     * Set the instance's day
     * @param int $value
     * @return static
     */
    public function jDay($value)
    {
        $this->jDay = $value;

        return $this;
    }


    public function jSetDate($year, $month, $day)
    {
        static::fixWraps($year, $month, $day);

        $G = static::toGregorian($year, $month, $day);

        return self::setDate($G[0], $G[1], $G[2]);
    }

    /**
     * Set the date and time all together
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return static
     */
    public function jSetDateTime($year, $month, $day, $hour, $minute, $second = 0)
    {
        return $this->jSetDate($year, $month, $day)->setTime($hour, $minute, $second);
    }
    ///////////////////////////////////////////////////////////////////
    /////////////////////// WEEK SPECIAL DAYS /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Get the first day of week
     * @return int
     */
    public static function jGetWeekStartsAt()
    {
        return static::$jWeekStartsAt;
    }

    /**
     * Set the first day of week
     * @param int
     */
    public static function jSetWeekStartsAt($day)
    {
        static::$jWeekStartsAt = $day;
    }

    /**
     * Get the last day of week
     * @return int
     */
    public static function jGetWeekEndsAt()
    {
        return static::$jWeekEndsAt;
    }

    /**
     * Set the last day of week
     * @param int
     */
    public static function setWeekEndsAt($day)
    {
        static::$jWeekEndsAt = $day;
    }

    /**
     * Get weekend days
     * @return array
     */
    public static function jGetWeekendDays()
    {
        return static::$jWeekendDays;
    }

    /**
     * Set weekend days
     * @param array
     */
    public static function jSetWeekendDays($days)
    {
        static::$jWeekendDays = $days;
    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////// TESTING AIDS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    // Not needed

    ///////////////////////////////////////////////////////////////////
    /////////////////////// LOCALIZATION //////////////////////////////
    ///////////////////////////////////////////////////////////////////

    // Todo: Maybe we could add a translator right here. right?

    /**
     * Convert Latin numbers to farsi numbers
     * @param string $string
     * @return string
     */
    public static function farsiNum($string)
    {
        return static::convertNumbers($string);
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////// STRING FORMATTING /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Format the instance as date
     * @return string
     */
    public function jToDateString()
    {
        return $this->jFormat('Y-m-d');
    }

    /**
     * Format the instance as a readable date
     * @return string
     */
    public function jToFormattedDateString()
    {
        return $this->jFormat('j M, Y');
    }

    /**
     * Format the instance as time
     * @return string
     */
    public function jToTimeString()
    {
        return $this->toTimeString();
    }

    /**
     * Format the instance as date and time
     * @return string
     */
    public function jToDateTimeString()
    {
        return $this->jFormat('Y-m-d H:i:s');
    }

    /**
     * Format the instance with day, date and time
     * @return string
     */
    public function jToDayDateTimeString()
    {
        return $this->jFormat('D, j M Y, g:i A');
    }

    /**
     * Format the instance as ATOM
     * @return string
     */
    public function jToAtomString()
    {
        return $this->jFormat(static::ATOM);
    }

    /**
     * Format the instance as COOKIE
     * @return string
     */
    public function jToCookieString()
    {
        return $this->jFormat(static::COOKIE);
    }

    /**
     * Format the instance as ISO8601
     * @return string
     */
    public function jToIso8601String()
    {
        return $this->jToAtomString();
    }

    /**
     * Format the instance as RFC822
     * @return string
     */
    public function jToRfc822String()
    {
        return $this->jFormat(static::RFC822);
    }

    /**
     * Format the instance as RFC850
     * @return string
     */
    public function jToRfc850String()
    {
        return $this->jFormat(static::RFC850);
    }

    /**
     * Format the instance as RFC1036
     * @return string
     */
    public function jToRfc1036String()
    {
        return $this->jFormat(static::RFC1036);
    }

    /**
     * Format the instance as RFC1123
     * @return string
     */
    public function jToRfc1123String()
    {
        return $this->jFormat(static::RFC1123);
    }

    /**
     * Format the instance as RFC2822
     * @return string
     */
    public function jToRfc2822String()
    {
        return $this->jFormat(static::RFC2822);
    }

    /**
     * Format the instance as RFC3339
     * @return string
     */
    public function jToRfc3339String()
    {
        return $this->jFormat(static::RFC3339);
    }

    /**
     * Format the instance as RSS
     * @return string
     */
    public function jToRssString()
    {
        return $this->jFormat(static::RSS);
    }

    /**
     * Format the instance as W3C
     * @return string
     */
    public function jToW3cString()
    {
        return $this->jFormat(static::W3C);
    }


    ///////////////////////////////////////////////////////////////////
    ////////////////////////// COMPARISONS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    // Only these Methods are needed

    /**
     * Determines if the instance is a weekday
     *
     * @return bool
     */
    public function jIsWeekday()
    {
        return !$this->jIsWeekend();
    }

    /**
     * Determines if the instance is a weekend day
     *
     * @return bool
     */
    public function jIsWeekend()
    {
        return in_array($this->dayOfWeek, static::$jWeekendDays);
    }

    /**
     * Determines if the instance is a leap year
     *
     * @return bool
     */
    public function jIsLeapYear()
    {
        return static::isLeapJalaliYear($this->jYear);
    }


    /**
     * Determines if the instance is a long year.
     * In other word, this year has 53 weeks, but normal years have 52 weeks
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Week_dates
     *
     * @return bool
     */
    public function jIsLongYear()
    {
        $M12Days = static::jDaysInMonth((int)$this->jFormat('Y'), 12);
        return static::jCreate((int)$this->jFormat('Y'), 12, $M12Days, 0, 0, 0, $this->tz)->jWeekOfYear === 53;
    }

    public function jIsSameAs($format, Carbon $dt = null)
    {
        /** @var Carbon $dt */
        $dt = $dt ?: static::now($this->tz);
        return $this->jFormat($format) === $dt->jFormat($format);
    }

    /**
     * Determines if the instance is in the current year
     *
     * @return bool
     */
    public function jIsCurrentYear()
    {
        return $this->jIsSameYear();
    }

    /**
     * Checks if the passed in date is in the same year as the instance year.
     *
     * @param \Carbon\Carbon|null $dt The instance to compare with or null to use current day.
     *
     * @return bool
     */
    public function jIsSameYear(Carbon $dt = null)
    {
        return $this->jIsSameAs('Y', $dt);
    }

    /**
     * Determines if the instance is in the current month
     *
     * @return bool
     */
    public function jIsCurrentMonth()
    {
        return $this->jIsSameMonth();
    }

    /**
     * Checks if the passed in date is in the same month as the instance month (and year if needed).
     *
     * @param \Carbon\Carbon|null $dt The instance to compare with or null to use current day.
     * @param bool $ofSameYear Check if it is the same month in the same year.
     *
     * @return bool
     */
    public function jIsSameMonth(Carbon $dt = null, $ofSameYear = false)
    {
        $format = $ofSameYear ? 'Y-m' : 'm';

        return $this->jIsSameAs($format, $dt);
    }

    /**
     * Checks if the passed in date is the same day as the instance current day.
     *
     * @param \Carbon\Carbon $dt
     *
     * @return bool
     */
    public function jIsSameDay(Carbon $dt)
    {
        return $this->toDateString() === $dt->toDateString();
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////// ADDITIONS AND SUBTRACTIONS ////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Add years to the instance. Positive $value travel forward while
     * negative $value travel into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddYears($value)
    {
        // Todo: does not work
        return $this->jModify((int) $value.' year');
    }

    /**
     * Add a year to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddYear($value = 1)
    {
        return $this->jAddYears($value);
    }

    /**
     * Remove years from the instance.
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubYears($value)
    {
        return $this->jAddYears(-1 * $value);
    }

    /**
     * Remove a year from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubYear($value = 1)
    {
        return $this->jSubYears($value);
    }

    /**
     * Add quarters to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddQuarters($value)
    {
        return $this->jAddMonths(static::MONTHS_PER_QUARTER * $value);
    }

    /**
     * Add a quarter to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddQuarter($value = 1)
    {
        return $this->jAddQuarters($value);
    }

    /**
     * Remove quarters from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubQuarters($value)
    {
        return $this->jAddQuarters(-1 * $value);
    }

    /**
     * Remove a quarter from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubQuarter($value = 1)
    {
        return $this->jSubQuarters($value);
    }

    /**
     * Add centuries to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddCenturies($value)
    {
        return $this->jAddYears(static::YEARS_PER_CENTURY * $value);
    }

    /**
     * Add a century to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddCentury($value = 1)
    {
        return $this->jAddCenturies($value);
    }

    /**
     * Remove centuries from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubCenturies($value)
    {
        return $this->jAddCenturies(-1 * $value);
    }

    /**
     * Remove a century from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubCentury($value = 1)
    {
        return $this->jAddCenturies($value);
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonths($value)
    {
        if (static::shouldOverflowMonths()) {
            return $this->jAddMonthsWithOverflow($value);
        }

        return $this->jAddMonthsNoOverflow($value);
    }

    /**
     * Add a month to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonth($value = 1)
    {
        return $this->jAddMonths($value);
    }

    /**
     * Remove months from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonths($value)
    {
        return $this->jAddMonths(-1 * $value);
    }

    /**
     * Remove a month from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonth($value = 1)
    {
        return $this->jSubMonths($value);
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonthsWithOverflow($value)
    {
        // Todo: does not work
        return $this->jModify((int) $value.' month');
    }

    /**
     * Add a month to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonthWithOverflow($value = 1)
    {
        return $this->jAddMonthsWithOverflow($value);
    }

    /**
     * Remove months from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonthsWithOverflow($value)
    {
        return $this->jAddMonthsWithOverflow(-1 * $value);
    }

    /**
     * Remove a month from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonthWithOverflow($value = 1)
    {
        return $this->jSubMonthsWithOverflow($value);
    }


    /**
     * Add months without overflowing to the instance. Positive $value
     * travels forward while negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonthsNoOverflow($value)
    {
        $day = $this->day;

        $this->jModify((int) $value.' month');

        if ($day !== $this->day) {
            $this->jModify('last day of previous month');
        }

        return $this;
    }

    /**
     * Add a month with no overflow to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddMonthNoOverflow($value = 1)
    {
        return $this->jAddMonthsNoOverflow($value);
    }

    /**
     * Remove months with no overflow from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonthsNoOverflow($value)
    {
        return $this->jAddMonthsNoOverflow(-1 * $value);
    }

    /**
     * Remove a month with no overflow from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubMonthNoOverflow($value = 1)
    {
        return $this->jSubMonthsNoOverflow($value);
    }

    /**
     * Add days to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddDays($value)
    {
        return $this->addDays($value);
    }

    /**
     * Add a day to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddDay($value = 1)
    {
        return $this->addDays($value);
    }

    /**
     * Remove a day from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubDay($value = 1)
    {
        return $this->subDays($value);
    }

    /**
     * Remove days from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubDays($value)
    {
        return $this->addDays(-1 * $value);
    }





    /**
     * Add weekdays to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddWeekdays($value)
    {
        // Todo: make sure this works woth Jalali
        // fix for https://bugs.php.net/bug.php?id=54909
        $t = $this->toTimeString();
        $this->jModify((int) $value.' weekday');

        return $this->setTimeFromTimeString($t);
    }

    /**
     * Add a weekday to the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jAddWeekday($value = 1)
    {
        return $this->jAddWeekdays($value);
    }

    /**
     * Remove weekdays from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubWeekdays($value)
    {
        return $this->jAddWeekdays(-1 * $value);
    }

    /**
     * Remove a weekday from the instance
     *
     * @param int $value
     *
     * @return static
     */
    public function jSubWeekday($value = 1)
    {
        return $this->jSubWeekdays($value);
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////////// DIFFERENCES ///////////////////////////
    ///////////////////////////////////////////////////////////////////

    // Todo: Is it needed? Make some tests
    // Maybe these functions are needed
    // diffInYears
    // diffInMonths
    // diffInWeekdays
    // diffInWeekendDays

    // Todo: this is needed indeed -> diffForHumans


    ///////////////////////////////////////////////////////////////////
    //////////////////////////// MODIFIERS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Resets the date to the first day of the month and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfMonth()
    {
        return $this->jSetDateTime($this->jYear, $this->jMonth, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the month and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfMonth()
    {
        return $this->jSetDateTime($this->jYear, $this->jMonth, static::jDaysInMonth($this->jYear, $this->jMonth), 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the quarter and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfQuarter()
    {
        // Todo: test of this method works fine
        $month = ($this->jQuarter - 1) * static::MONTHS_PER_QUARTER + 1;

        return $this->jSetDateTime($this->jYear, $month, 1, 0, 0, 0);
    }
    /**
     * Resets the date to end of the quarter and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfQuarter()
    {
        return $this->jStartOfQuarter()->jAddMonths(static::MONTHS_PER_QUARTER - 1)->jEndOfMonth();
    }

    /**
     * Resets the date to the first day of the year and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfYear()
    {
        return $this->jSetDateTime($this->jYear, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the year and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfYear()
    {
        return $this->jSetDateTime($this->jYear, 12, static::jDaysInMonth($this->jYear, 12), 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the decade and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfDecade()
    {
        $year = $this->jYear - $this->jYear % static::YEARS_PER_DECADE;

        return $this->jSetDateTime($year, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the decade and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfDecade()
    {
        $year = $this->jYear - $this->jYear % static::YEARS_PER_DECADE + static::YEARS_PER_DECADE - 1;

        return $this->jSetDateTime($year, 12, static::jDaysInMonth($year, 12), 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the century and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfCentury()
    {
        $year = $this->jYear - ($this->jYear - 1) % static::YEARS_PER_CENTURY;

        return $this->jSetDateTime($year, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the century and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfCentury()
    {
        $year = $this->jYear - 1 - ($this->jYear - 1) % static::YEARS_PER_CENTURY + static::YEARS_PER_CENTURY;

        return $this->jSetDateTime($year, 12, static::jDaysInMonth($year, 12), 23, 59, 59);
    }

    /**
     * Resets the date to the first day of week (defined in $weekStartsAt) and the time to 00:00:00
     *
     * @return static
     */
    public function jStartOfWeek()
    {
        while ($this->dayOfWeek !== static::$jWeekStartsAt) {
            $this->subDay();
        }

        return $this->startOfDay();
    }

    /**
     * Resets the date to end of week (defined in $weekEndsAt) and time to 23:59:59
     *
     * @return static
     */
    public function jEndOfWeek()
    {
        while ($this->dayOfWeek !== static::$jWeekEndsAt) {
            $this->addDay();
        }

        return $this->endOfDay();
    }

    /**
     * Modify to the next occurrence of a given day of the week.
     * If no dayOfWeek is provided, modify to the next occurrence
     * of the current day of the week.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jNext($dayOfWeek = null)
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }

        return $this->startOfDay()->jModify('next '.static::$days[$dayOfWeek]);
    }

    /**
     * Modify to the previous occurrence of a given day of the week.
     * If no dayOfWeek is provided, modify to the previous occurrence
     * of the current day of the week.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jPrevious($dayOfWeek = null)
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }

        return $this->startOfDay()->jModify('last '.static::$days[$dayOfWeek]);
    }
    /**
     * Go forward or backward to the next week- or weekend-day.
     *
     * @param bool $weekday
     * @param bool $forward
     *
     * @return static
     */

    private function jNextOrPreviousDay($weekday = true, $forward = true)
    {
        $step = $forward ? 1 : -1;

        do {
            $this->addDay($step);
        } while ($weekday ? $this->jIsWeekend() : $this->jIsWeekday());

        return $this;
    }


    /**
     * Go forward to the next weekday.
     *
     * @return $this
     */
    public function jNextWeekday()
    {
        return $this->jNextOrPreviousDay();
    }

    /**
     * Go backward to the previous weekday.
     *
     * @return static
     */
    public function jPreviousWeekday()
    {
        return $this->jNextOrPreviousDay(true, false);
    }

    /**
     * Go forward to the next weekend day.
     *
     * @return static
     */
    public function jNextWeekendDay()
    {
        return $this->jNextOrPreviousDay(false);
    }

    /**
     * Go backward to the previous weekend day.
     *
     * @return static
     */
    public function jPreviousWeekendDay()
    {
        return $this->jNextOrPreviousDay(false, false);
    }


    /**
     * Modify to the first occurrence of a given day of the week
     * in the current month. If no dayOfWeek is provided, modify to the
     * first day of the current month.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jFirstOfMonth($dayOfWeek = null)
    {
        $this->startOfDay();
        if ($dayOfWeek === null) {
            return $this->jDay(1);
        }

        return $this->jModify('first '.static::$days[$dayOfWeek].' of '.$this->jFormat('F').' '.$this->jYear);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current month. If no dayOfWeek is provided, modify to the
     * last day of the current month.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jLastOfMonth($dayOfWeek = null)
    {
        $this->startOfDay();

        if ($dayOfWeek === null) {
            return $this->jDay($this->jDaysInMonth);
        }

        return $this->jModify('last '.static::$days[$dayOfWeek].' of '.$this->jFormat('F').' '.$this->year);
    }

    /**
     * Modify to the given occurrence of a given day of the week
     * in the current month. If the calculated occurrence is outside the scope
     * of the current month, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int $nth
     * @param int $dayOfWeek
     *
     * @return mixed
     */
    public function jNthOfMonth($nth, $dayOfWeek)
    {
        // Todo: How does this work anyway?
        $dt = $this->copy()->jFirstOfMonth();
        $check = $dt->jFormat('Y-m');
        $dt->jModify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return $dt->jFormat('Y-m') === $check ? $this->jModify($dt) : false;
    }

    /**
     * Modify to the first occurrence of a given day of the week
     * in the current quarter. If no dayOfWeek is provided, modify to the
     * first day of the current quarter.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jFirstOfQuarter($dayOfWeek = null)
    {
        return $this->jSetDate($this->jYear, $this->jQuarter * static::MONTHS_PER_QUARTER - 2, 1)->jFirstOfMonth($dayOfWeek);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current quarter. If no dayOfWeek is provided, modify to the
     * last day of the current quarter.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jLastOfQuarter($dayOfWeek = null)
    {
        return $this->jSetDate($this->jYear, $this->jQuarter * static::MONTHS_PER_QUARTER, 1)->jLastOfMonth($dayOfWeek);
    }


    /**
     * Modify to the given occurrence of a given day of the week
     * in the current quarter. If the calculated occurrence is outside the scope
     * of the current quarter, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int $nth
     * @param int $dayOfWeek
     *
     * @return mixed
     */
    public function nthOfQuarter($nth, $dayOfWeek)
    {
        $dt = $this->copy()->jDay(1)->jMonth($this->jQuarter * static::MONTHS_PER_QUARTER);
        $lastMonth = $dt->jMonth;
        $year = $dt->jYear;
        $dt->jFirstOfQuarter()->jModify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return ($lastMonth < $dt->month || $year !== $dt->year) ? false : $this->jModify($dt);
    }

    /**
     * Modify to the first occurrence of a given day of the week
     * in the current year. If no dayOfWeek is provided, modify to the
     * first day of the current year.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function jFirstOfYear($dayOfWeek = null)
    {
        return $this->jMonth(1)->jFirstOfMonth($dayOfWeek);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current year. If no dayOfWeek is provided, modify to the
     * last day of the current year.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int|null $dayOfWeek
     *
     * @return static
     */
    public function lastOfYear($dayOfWeek = null)
    {
        return $this->jMonth(static::MONTHS_PER_YEAR)->jLastOfMonth($dayOfWeek);
    }

    /**
     * Modify to the given occurrence of a given day of the week
     * in the current year. If the calculated occurrence is outside the scope
     * of the current year, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @param int $nth
     * @param int $dayOfWeek
     *
     * @return mixed
     */
    public function jNthOfYear($nth, $dayOfWeek)
    {
        $dt = $this->copy()->jFirstOfYear()->jModify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return $this->jYear === $dt->jYear ? $this->jModify($dt) : false;
    }

    /**
     * Check if its the birthday. Compares the date/month values of the two dates.
     *
     * @param \Carbon\Carbon|null $dt The instance to compare with or null to use current day.
     *
     * @return bool
     */
    public function jIsBirthday(Carbon $dt = null)
    {
        return $this->jIsSameAs('md', $dt);
    }




























    ///////////////////////////////////////////////////////////////////
    //////////////////////////// jDateTime ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    private static $jDateTemp;

    /**
     * Converts a Gregorian date to Jalali.
     * @param $gy
     * @param $gm
     * @param $gd
     * @return array
     * 0: Year
     * 1: Month
     * 2: Day
     */
    protected static function toJalali($gy, $gm, $gd)
    {
        return self::d2j(self::g2d($gy, $gm, $gd));
    }

    /**
     * Converts a Jalali date to Gregorian.
     * @param int $jy
     * @param int $jm
     * @param int $jd
     * @return array
     * 0: Year
     * 1: Month
     * 2: Day
     */
    protected static function toGregorian($jy, $jm, $jd)
    {
        return self::d2g(self::j2d($jy, $jm, $jd));
    }

    /**
     * Checks whether a Jalaali date is valid or not.
     * @param int $jy
     * @param int $jm
     * @param int $jd
     * @return bool
     */
    protected static function isValidateJalaliDate($jy, $jm, $jd)
    {
        return $jy >= -61 && $jy <= 3177
            && $jm >= 1 && $jm <= 12
            && $jd >= 1 && $jd <= self::jDaysInMonth($jy, $jm);
    }

    /**
     * Checks whether a date is valid or not.
     * @param      $year
     * @param      $month
     * @param      $day
     * @param bool $isJalali
     * @return bool
     */
    protected static function checkDate($year, $month, $day, $isJalali = true)
    {
        return $isJalali === true ? self::isValidateJalaliDate($year, $month,
            $day) : checkdate($month, $day, $year);
    }

    /**
     *  Is this a leap year or not?
     * @param $jy
     * @return bool
     */
    protected static function isLeapJalaliYear($jy)
    {
        return self::jalaliCal($jy)['leap'] === 0;
    }


    /**
     * This function determines if the Jalaali (Persian) year is
     * leap (366-day long) or is the common year (365 days), and
     * finds the day in March (Gregorian calendar) of the first
     * day of the Jalaali year (jy).
     * @param int $jy Jalaali calendar year (-61 to 3177)
     * @return array
     * leap: number of years since the last leap year (0 to 4)
     * gy: Gregorian year of the beginning of Jalaali year
     * march: the March day of Farvardin the 1st (1st day of jy)
     * @see: http://www.astro.uni.torun.pl/~kb/Papers/EMP/PersianC-EMP.htm
     * @see: http://www.fourmilab.ch/documents/calendar/
     */
    protected static function jalaliCal($jy)
    {
        $breaks = [
            -61,
            9,
            38,
            199,
            426,
            686,
            756,
            818,
            1111,
            1181,
            1210
            ,
            1635,
            2060,
            2097,
            2192,
            2262,
            2324,
            2394,
            2456,
            3178
        ];

        $breaksCount = count($breaks);

        $gy = $jy + 621;
        $leapJ = -14;
        $jp = $breaks[0];

        if ($jy < $jp || $jy >= $breaks[$breaksCount - 1]) {
            throw new \InvalidArgumentException('Invalid Jalali year : ' . $jy);
        }

        $jump = 0;

        for ($i = 1; $i < $breaksCount; $i += 1) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;

            if ($jy < $jm) {
                break;
            }

            $leapJ = $leapJ + self::div($jump, 33) * 8 + self::div(self::mod($jump,
                    33), 4);

            $jp = $jm;
        }

        $n = $jy - $jp;

        $leapJ = $leapJ + self::div($n, 33) * 8 + self::div(self::mod($n, 33) + 3,
                4);

        if (self::mod($jump, 33) === 4 && $jump - $n === 4) {
            $leapJ += 1;
        }

        $leapG = self::div($gy, 4) - self::div((self::div($gy, 100) + 1) * 3,
                4) - 150;

        $march = 20 + $leapJ - $leapG;

        if ($jump - $n < 6) {
            $n = $n - $jump + self::div($jump + 4, 33) * 33;
        }

        $leap = self::mod(self::mod($n + 1, 33) - 1, 4);

        if ($leap === -1) {
            $leap = 4;
        }

        return [
            'leap' => $leap,
            'gy' => $gy,
            'march' => $march
        ];
    }

    /**
     * @param $a
     * @param $b
     * @return float
     */
    protected static function div($a, $b)
    {
        return ~~($a / $b);
    }

    /**
     * @param $a
     * @param $b
     * @return mixed
     */
    protected static function mod($a, $b)
    {
        return $a - ~~($a / $b) * $b;
    }

    /**
     * @param $jdn
     * @return array
     */
    protected static function d2g($jdn)
    {
        $j = 4 * $jdn + 139361631;
        $j += self::div(self::div(4 * $jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        $i = self::div(self::mod($j, 1461), 4) * 5 + 308;

        $gd = self::div(self::mod($i, 153), 5) + 1;
        $gm = self::mod(self::div($i, 153), 12) + 1;
        $gy = self::div($j, 1461) - 100100 + self::div(8 - $gm, 6);

        return [$gy, $gm, $gd];
    }

    /**
     * Calculates the Julian Day number from Gregorian or Julian
     * calendar dates. This integer number corresponds to the noon of
     * the date (i.e. 12 hours of Universal Time).
     * The procedure was tested to be good since 1 March, -100100 (of both
     * calendars) up to a few million years into the future.
     * @param int $gy Calendar year (years BC numbered 0, -1, -2, ...)
     * @param int $gm Calendar month (1 to 12)
     * @param int $gd Calendar day of the month (1 to 28/29/30/31)
     * @return int Julian Day number
     */
    protected static function g2d($gy, $gm, $gd)
    {
        return (
                self::div(($gy + self::div($gm - 8, 6) + 100100) * 1461, 4)
                + self::div(153 * self::mod($gm + 9, 12) + 2, 5)
                + $gd - 34840408
            ) - self::div(self::div($gy + 100100 + self::div($gm - 8, 6),
                    100) * 3, 4) + 752;

    }

    /**
     * Converts a date of the Jalaali calendar to the Julian Day number.
     * @param int $jy Jalaali year (1 to 3100)
     * @param int $jm Jalaali month (1 to 12)
     * @param int $jd Jalaali day (1 to 29/31)
     * @return int  Julian Day number
     */
    protected static function j2d($jy, $jm, $jd)
    {
        $jCal = self::jalaliCal($jy);

        return self::g2d($jCal['gy'], 3,
                $jCal['march']) + ($jm - 1) * 31 - self::div($jm,
                7) * ($jm - 7) + $jd - 1;
    }


    /**
     * Converts the Julian Day number to a date in the Jalaali calendar.
     * @param int $jdn Julian Day number
     * @return array
     * 0: Jalaali year (1 to 3100)
     * 1: Jalaali month (1 to 12)
     * 2: Jalaali day (1 to 29/31)
     */
    protected static function d2j($jdn)
    {
        $gy = self::d2g($jdn)[0];
        $jy = $gy - 621;
        $jCal = self::jalaliCal($jy);
        $jdn1f = self::g2d($gy, 3, $jCal['march']);

        $k = $jdn - $jdn1f;

        if ($k >= 0) {
            if ($k <= 185) {
                $jm = 1 + self::div($k, 31);
                $jd = self::mod($k, 31) + 1;

                return [$jy, $jm, $jd];
            } else {
                $k -= 186;
            }
        } else {
            $jy -= 1;
            $k += 179;

            if ($jCal['leap'] === 1) {
                $k += 1;
            }
        }

        $jm = 7 + self::div($k, 30);
        $jd = self::mod($k, 30) + 1;

        return [$jy, $jm, $jd];
    }

    /**
     * @param      $format
     * @param bool $stamp
     * @param bool $timezone
     * @return mixed
     */
    protected static function date($format, $stamp = false, $timezone = null)
    {
        $stamp = ($stamp !== false) ? $stamp : time();
        $dateTime = static::createDateTime($stamp, $timezone);


        //Find what to replace
        $chars = (preg_match_all('/([a-zA-Z]{1})/', $format,
            $chars)) ? $chars[0] : [];

        //Intact Keys
        $intact = [
            'B',
            'h',
            'H',
            'g',
            'G',
            'i',
            's',
            'I',
            'U',
            'u',
            'Z',
            'O',
            'P'
        ];
        $intact = self::filterArray($chars, $intact);
        $intactValues = [];

        foreach ($intact as $k => $v) {
            $intactValues[$k] = $dateTime->format($v);
        }
        //End Intact Keys

        //Changed Keys
        list($year, $month, $day) = [
            $dateTime->format('Y'),
            $dateTime->format('n'),
            $dateTime->format('j')
        ];
        list($jYear, $jMonth, $jDay) = self::toJalali($year, $month, $day);

        $keys = [
            'd',
            'D',
            'j',
            'l',
            'N',
            'S',
            'w',
            'z',
            'W',
            'F',
            'm',
            'M',
            'n',
            't',
            'L',
            'o',
            'Y',
            'y',
            'a',
            'A',
            'c',
            'r',
            'e',
            'T'
        ];
        $keys = self::filterArray($chars, $keys, ['z']);
        $values = [];

        foreach ($keys as $k => $key) {

            $v = '';
            switch ($key) {
                //Day
                case 'd':
                    $v = sprintf("%02d", $jDay);
                    break;
                case 'D':
                    $v = self::getDayNames($dateTime->format('D'), true);
                    break;
                case 'j':
                    $v = $jDay;
                    break;
                case 'l':
                    $v = self::getDayNames($dateTime->format('l'));
                    break;
                case 'N':
                    $v = self::getDayNames($dateTime->format('l'), false, 1, true);
                    break;
                case 'S':
                    $v = 'ام';
                    break;
                case 'w':
                    $v = self::getDayNames($dateTime->format('l'), false, 1,
                            true) - 1;
                    break;
                case 'z':
                    if ($jMonth > 6) {
                        $v = 186 + (($jMonth - 6 - 1) * 30) + $jDay;
                    } else {
                        $v = (($jMonth - 1) * 31) + $jDay;
                    }
                    self::$jDateTemp['z'] = $v;
                    break;
                //Week
                case 'W':
                    $v = is_int(self::$jDateTemp['z'] / 7) ? (self::$jDateTemp['z'] / 7) : intval(self::$jDateTemp['z'] / 7 + 1);
                    break;
                //Month
                case 'F':
                    $v = self::getMonthNames($jMonth);
                    break;
                case 'm':
                    $v = sprintf("%02d", $jMonth);
                    break;
                case 'M':
                    $v = self::getMonthNames($jMonth, true);
                    break;
                case 'n':
                    $v = $jMonth;
                    break;
                case 't':
                    $v = ($jMonth == 12) ? 29 : (($jMonth > 6 && $jMonth != 12) ? 30 : 31);
                    break;
                //Year
                case 'L':
                    $tmpObj = static::createDateTime(time() - 31536000, $timezone);
                    $v = $tmpObj->format('L');
                    break;
                case 'o':
                case 'Y':
                    $v = $jYear;
                    break;
                case 'y':
                    $v = $jYear % 100;
                    break;
                //Time
                case 'a':
                    $v = ($dateTime->format('a') == 'am') ? 'ق.ظ' : 'ب.ظ';
                    break;
                case 'A':
                    $v = ($dateTime->format('A') == 'AM') ? 'قبل از ظهر' : 'بعد از ظهر';
                    break;
                //Full Dates
                case 'c':
                    $v = $jYear . '-' . sprintf("%02d",
                            $jMonth) . '-' . sprintf("%02d", $jDay) . 'T';
                    $v .= $dateTime->format('H') . ':' . $dateTime->format('i') . ':' . $dateTime->format('s') . $dateTime->format('P');
                    break;
                case 'r':
                    $v = self::getDayNames($dateTime->format('D'),
                            true) . ', ' . sprintf("%02d",
                            $jDay) . ' ' . self::getMonthNames($jMonth, true);
                    $v .= ' ' . $jYear . ' ' . $dateTime->format('H') . ':' . $dateTime->format('i') . ':' . $dateTime->format('s') . ' ' . $dateTime->format('P');
                    break;
                //Timezone
                case 'e':
                    $v = $dateTime->format('e');
                    break;
                case 'T':
                    $v = $dateTime->format('T');
                    break;

            }
            $values[$k] = $v;

        }
        //End Changed Keys

        //Merge
        $keys = array_merge($intact, $keys);
        $values = array_merge($intactValues, $values);

        return strtr($format, array_combine($keys, $values));
    }

    /**
     * @param      $format
     * @param bool $stamp
     * @param null $timezone
     * @return mixed
     */
    protected static function strftime($format, $stamp = false, $timezone = null)
    {
        $str_format_code = [
            "%a",
            "%A",
            "%d",
            "%e",
            "%j",
            "%u",
            "%w",
            "%U",
            "%V",
            "%W",
            "%b",
            "%B",
            "%h",
            "%m",
            "%C",
            "%g",
            "%G",
            "%y",
            "%Y",
            "%H",
            "%I",
            "%l",
            "%M",
            "%p",
            "%P",
            "%r",
            "%R",
            "%S",
            "%T",
            "%X",
            "%z",
            "%Z",
            "%c",
            "%D",
            "%F",
            "%s",
            "%x",
            "%n",
            "%t",
            "%%",
        ];

        $date_format_code = [
            "D",
            "l",
            "d",
            "j",
            "z",
            "N",
            "w",
            "W",
            "W",
            "W",
            "M",
            "F",
            "M",
            "m",
            "y",
            "y",
            "y",
            "y",
            "Y",
            "H",
            "h",
            "g",
            "i",
            "A",
            "a",
            "h:i:s A",
            "H:i",
            "s",
            "H:i:s",
            "h:i:s",
            "H",
            "H",
            "D j M H:i:s",
            "d/m/y",
            "Y-m-d",
            "U",
            "d/m/y",
            "\n",
            "\t",
            "%",
        ];

        //Change Strftime format to Date format
        $format = str_replace($str_format_code, $date_format_code, $format);

        //Convert to date
        return self::date($format, $stamp, $timezone);
    }

    private static function getDayNames(
        $day,
        $shorten = false,
        $len = 1,
        $numeric = false
    ) {
        switch (strtolower($day)) {
            case 'sat':
            case 'saturday':
                $ret = 'شنبه';
                $n = 1;
                break;
            case 'sun':
            case 'sunday':
                $ret = 'یکشنبه';
                $n = 2;
                break;
            case 'mon':
            case 'monday':
                $ret = 'دوشنبه';
                $n = 3;
                break;
            case 'tue':
            case 'tuesday':
                $ret = 'سه‌شنبه';
                $n = 4;
                break;
            case 'wed':
            case 'wednesday':
                $ret = 'چهارشنبه';
                $n = 5;
                break;
            case 'thu':
            case 'thursday':
                $ret = 'پنجشنبه';
                $n = 6;
                break;
            case 'fri':
            case 'friday':
                $ret = 'جمعه';
                $n = 7;
                break;
            default:
                $ret = '';
                $n = -1;
        }

        return ($numeric) ? $n : (($shorten) ? mb_substr($ret, 0, $len,
            'UTF-8') : $ret);
    }

    private static function getMonthNames($month, $shorten = false, $len = 3)
    {
        $ret = '';
        switch ($month) {
            case '1':
                $ret = 'فروردین';
                break;
            case '2':
                $ret = 'اردیبهشت';
                break;
            case '3':
                $ret = 'خرداد';
                break;
            case '4':
                $ret = 'تیر';
                break;
            case '5':
                $ret = 'مرداد';
                break;
            case '6':
                $ret = 'شهریور';
                break;
            case '7':
                $ret = 'مهر';
                break;
            case '8':
                $ret = 'آبان';
                break;
            case '9':
                $ret = 'آذر';
                break;
            case '10':
                $ret = 'دی';
                break;
            case '11':
                $ret = 'بهمن';
                break;
            case '12':
                $ret = 'اسفند';
                break;
        }

        return ($shorten) ? mb_substr($ret, 0, $len, 'UTF-8') : $ret;
    }

    private static function filterArray($needle, $haystack, $always = [])
    {
        foreach ($haystack as $k => $v) {
            if (!in_array($v, $needle) && !in_array($v, $always)) {
                unset($haystack[$k]);
            }

        }


        return $haystack;
    }


    /**
     * @param $format
     * @param $date
     * @return array
     */
    protected static function parseFromFormat($format, $date)
    {
        // reverse engineer date formats
        $keys = [
            'Y' => ['year', '\d{4}'],
            'y' => ['year', '\d{2}'],
            'm' => ['month', '\d{2}'],
            'n' => ['month', '\d{1,2}'],
            'M' => ['month', '[A-Z][a-z]{3}'],
            'F' => ['month', '[A-Z][a-z]{2,8}'],
            'd' => ['day', '\d{2}'],
            'j' => ['day', '\d{1,2}'],
            'D' => ['day', '[A-Z][a-z]{2}'],
            'l' => ['day', '[A-Z][a-z]{6,9}'],
            'u' => ['hour', '\d{1,6}'],
            'h' => ['hour', '\d{2}'],
            'H' => ['hour', '\d{2}'],
            'g' => ['hour', '\d{1,2}'],
            'G' => ['hour', '\d{1,2}'],
            'i' => ['minute', '\d{2}'],
            's' => ['second', '\d{2}'],
        ];

        // convert format string to regex
        $regex = '';
        $chars = str_split($format);
        foreach ($chars as $n => $char) {
            $lastChar = isset($chars[$n - 1]) ? $chars[$n - 1] : '';
            $skipCurrent = '\\' == $lastChar;
            if (!$skipCurrent && isset($keys[$char])) {
                $regex .= '(?P<' . $keys[$char][0] . '>' . $keys[$char][1] . ')';
            } else {
                if ('\\' == $char) {
                    $regex .= $char;
                } else {
                    $regex .= preg_quote($char);
                }
            }
        }

        $dt = [];
        $dt['error_count'] = 0;
        // now try to match it
        if (preg_match('#^' . $regex . '$#', $date, $dt)) {
            foreach ($dt as $k => $v) {
                if (is_int($k)) {
                    unset($dt[$k]);
                }
            }
            if (!static::checkdate($dt['month'], $dt['day'], $dt['year'],
                false)
            ) {
                $dt['error_count'] = 1;
            }
        } else {
            $dt['error_count'] = 1;
        }
        $dt['errors'] = [];
        $dt['fraction'] = '';
        $dt['warning_count'] = 0;
        $dt['warnings'] = [];
        $dt['is_localtime'] = 0;
        $dt['zone_type'] = 0;
        $dt['zone'] = 0;
        $dt['is_dst'] = '';

        if (strlen($dt['year']) == 2) {
            $now = self::forge('now');
            $x = $now->format('Y') - $now->format('y');
            $dt['year'] += $x;
        }

        $dt['year'] = isset($dt['year']) ? (int)$dt['year'] : 0;
        $dt['month'] = isset($dt['month']) ? (int)$dt['month'] : 0;
        $dt['day'] = isset($dt['day']) ? (int)$dt['day'] : 0;
        $dt['hour'] = isset($dt['hour']) ? (int)$dt['hour'] : 0;
        $dt['minute'] = isset($dt['minute']) ? (int)$dt['minute'] : 0;
        $dt['second'] = isset($dt['second']) ? (int)$dt['second'] : 0;

        return $dt;
    }

    /**
     * Convert Latin numbers to persian numbers
     * @param string $string
     * @return string
     */
    protected static function convertNumbers($string)
    {
        $farsi_array = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
        $english_array = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

        return str_replace($english_array, $farsi_array, $string);
    }

    /**
     * @param      $timestamp
     * @param null $timezone
     * @return Carbon
     */
    protected static function createDateTime($timestamp = null, $timezone = null)
    {
        $timezone = static::createTimeZone($timezone);

        if ($timestamp === null) {
            return Carbon::now($timezone);
        }

        if ($timestamp instanceof \DateTimeInterface) {
            return $timestamp;
        }

        if (is_string($timestamp)) {
            return new Carbon($timestamp, $timezone);
        }

        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestamp($timestamp, $timezone);
        }

        throw new \InvalidArgumentException('timestamp is not valid');
    }

    /**
     * @param null $timezone
     * @return \DateTimeZone|null
     */
    protected static function createTimeZone($timezone = null)
    {
        if ($timezone instanceof \DateTimeZone) {
            return $timezone;
        }

        if ($timezone === null) {
            return new \DateTimeZone(date_default_timezone_get());
        }

        if (is_string($timezone)) {
            return new \DateTimeZone($timezone);
        }

        throw new \InvalidArgumentException('timezone is not valid');

    }














    ///////////////////////////////////////////////////////////////////
    /////////////////////// Private Helpers ///////////////////////////
    ///////////////////////////////////////////////////////////////////


    /**
     * Convert Latin numbers to persian numbers
     * @param string $string
     * @return string
     */
    protected static function convertNumbersToEnglish($string)
    {
        $farsi_array = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
        $english_array = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

        return str_replace($farsi_array, $english_array, $string);
    }

    /**
     * Checks if current day is Holiday.
     *
     * Note: Fridays are indeed Holidays.
     * Maybe we can add more days like new year holidays here.
     *
     * @return bool
     */
    public function jIsHoliday()
    {
        return $this->isFriday();
    }

    /**
     * Alter the timestamp of a Carbon object by incrementing or decrementing
     * in a format accepted by strtotime(). Applies the modify to Jalali Date.
     *
     * @param string $str A date/time string. Valid formats are explained in <a href="http://www.php.net/manual/en/datetime.formats.php">Date and Time Formats</a>.
     * @return static|boolean Returns the Carbon object for method chaining or FALSE on failure.
     * @link http://php.net/manual/en/datetime.modify.php completely works with jalali absolute dates
     * @link http://php.net/manual/en/datetime.formats.relative.php relative dates are not fully supported yet
     */
    public function jModify($str)
    {
        $d = static::getJDateArray($this);
        $modifyIsDone = false;
        $str = static::convertNumbersToEnglish(trim(strtolower($str)));

        switch (true) {
            // Relative Day/Week/Weekday to a relative/absolute date
            case (preg_match('((the )?(first|last) (day|week|week ?day) of (.+))', $str, $matches)):
                $d = static::getJDateArray($this->jModify($matches[4]));
                switch ($matches[3]) {
                    case 'day':
                        $d['day'] = $matches[2] == 'first' ? 1 : static::jDaysInMonth($d['year'], $d['month']);
                        break;
                    case 'week day':
                    case 'weekday':
                        $d['day'] = $matches[2] == 'first' ? 1 : static::jDaysInMonth($d['year'], $d['month']);
                        $step = $matches[2] == 'first' ? 1 : -1;
                        while ($this->jIsHoliday()) {
                            $this->jModify($step . ' day');
                        }
                        $modifyIsDone = true;
                        break;

                }
                $str = str_replace($matches[0], '', $str);
                break;

            // Relative Day/Month
            case (preg_match('((last|next|previous|this) (month|year))', $str, $matches)):
                $str = str_replace($matches[0], '', $str);
                switch ($matches[1]) {
                    case 'next':
                        $d[$matches[2]]++;
                        break;
                    case 'last':
                    case 'previous':
                        $d[$matches[2]]--;
                        break;
                }
                break;

            // Absolute Date
            case (preg_match('(([0-9]{1,2} )?(' . implode('|', array_keys(static::$months)) . ')( [0-9]{1,4})?)', $str, $matches)):
                $d['year'] = isset($matches[3]) && !empty($matches[3]) ? (int)$matches[3] : $d['year'];
                $d['month'] = static::$months[strtolower($matches[2])];
                $d['day'] = empty($matches[1]) ? 1 : (int)$matches[1];
                $str = str_replace($matches[0], '', $str);
                break;

            // Absolute Date Time
            case (preg_match('(([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2}))', $str, $m)):
                $d = array('year' => $m[1], 'month' => $m[2], 'day' => $m[3], 'hour' => $m[4], 'minute' => $m[5], 'second' => $m[6]);
                $str = str_replace($m[0], '', $str);
                break;

            // Absolute Date
            case (preg_match('(([0-9]{4})-([0-9]{2})-([0-9]{2}))', $str, $m)):
                $str = str_replace($m[0], '', $str);
                $d['year'] = $m[1];
                $d['month'] = $m[2];
                $d['day'] = $m[3];
                break;

            // Relative Years
            case (preg_match('#(([-+]?[0-9]+) years?( ago)?)#', $str, $matches)):
                $str = str_replace($matches[0], '', $str);
//                $direction =
                $d['year'] += $matches[2];
                break;

            // Relative Months
            case (preg_match('/(([-+]?[0-9]+) months?( ago)?)/', $str, $matches)):
                $str = str_replace($matches[1], '', $str);
                $d['month'] += $matches[2];
                break;

            // Relative Weekdays
            case (preg_match('/(([-+]?[0-9]+) ?weekdays?( ago)?)/', $str, $matches)):
                $step = ($matches[2] < 0 ? -1 : 1) * (isset($matches[3]) ? -1 : 1);
                $i = 0;
                while ($i < $step * $matches[2]) {
                    $this->jModify($step . ' day');
                    $i += $this->jIsHoliday() ? 0 : 1;
                }
                $str = str_replace($matches[0], '', $str);
                $modifyIsDone = true;
                break;

            // Smaller than a Day Modifications
            default:
                $this->modify(trim($str));
                $str = '';
                $modifyIsDone = true;
        }


        if (!$modifyIsDone) {
            static::fixWraps($d['year'], $d['month'], $d['day'], $d['hour'], $d['minute'], $d['second']);

            $this->jSetDateTime($d['year'], $d['month'], $d['day'], $d['hour'], $d['minute'], $d['second']);
        }

        if (Carbon::hasRelativeKeywords(trim($str))) {
            $this->jModify($str);
        }

        return $this;
    }

    /**
     * Normalizes the given DateTime values and removes wraps.
     * @param null $year
     * @param null $month
     * @param null $day
     * @param null $hour
     * @param null $minute
     * @param null $second
     */
    protected static function fixWraps(
        &$year = null,
        &$month = null,
        &$day = null,
        &$hour = null,
        &$minute = null,
        &$second = null
    ) {
        if ($year < 0) {
            // Cannot handle dates less than 0.
            // converting negative years to Gregorian cause error
            $year = 0;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        } elseif ($year > 1876) {
            // Cannot handle dates grater than 1876
            $year = 1876;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        }

        // Month
        if ($month === null) {
            return;
        }
        if ($month <= 0) {
            $year = $year + (int)($month / 12) - 1;
            $month = 12 + $month % 12;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        } elseif ($month > 12) {
            $year = $year + (int)($month / 12);
            $month = $month % 12;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        }

        // Day
        if ($day === null) {
            return;
        }
        if ($day > ($daysInMonth = static::jDaysInMonth($year, $month))) {
            $day = $day - $daysInMonth;
            $month++;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);

        } elseif ($day == 0) {
            $day = static::daysInPreviousJalaliMonth($year, $month);
            $month--;
            static::fixWraps($year, $month, $day, $hour, $minute, $second);

        } elseif ($day < 0) {
            $pmDays = static::daysInPreviousJalaliMonth($year, $month);

            if ($day > -$pmDays) {
                $month--;
                $day = $pmDays + $day;
                static::fixWraps($year, $month, $day, $hour, $minute, $second);

            } elseif ($day <= -$pmDays) {
                $month--;
                $day = $day + $pmDays;
                static::fixWraps($year, $month, $day, $hour, $minute, $second);
            }
        }

        // Hour
        if ($hour === null) {
            return;
        }
        if ($hour >= 24) {
            $day = $day + (int)($hour / 24);
            $hour = ($hour % 24);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);

        } elseif ($hour < 0) {
            $day = $day + (int)($hour / 24) - 1;
            $hour = 24 + ($hour % 24);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        }

        // Minute
        if ($minute === null) {
            return;
        }
        if ($minute >= 60) {
            $hour = $hour + (int)($minute / 60);
            $minute = ($minute % 60);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);

        } elseif ($minute < 0) {
            $hour = $hour + (int)($minute / 60) - 1;
            $minute = 60 + ($minute % 60);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        }

        // Seconds
        if ($second === null) {
            return;
        }
        if ($second >= 60) {
            $minute = $minute + (int)($second / 60);
            $second = ($second % 60);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);

        } elseif ($second < 0) {
            $minute = $minute + (int)($second / 60) - 1;
            $second = 60 + ($second % 60);
            static::fixWraps($year, $month, $day, $hour, $minute, $second);
        }
    }

    protected static function daysInPreviousJalaliMonth($year, $month)
    {
        if ($month == 1) {
            return static::jDaysInMonth($year - 1, 12);
        }

        return static::jDaysInMonth($year, $month - 1);
    }

    /**
     * Number of days in a given month in a Jalaali year.
     * @param int $jy
     * @param int $jm
     * @return int
     */
    public static function jDaysInMonth($jy, $jm)
    {
        if ($jm <= 6) {
            return 31;
        }

        if ($jm <= 11) {
            return 30;
        }

        return self::isLeapJalaliYear($jy) ? 30 : 29;
    }

    private static function jNowIfNull(
        &$year = null,
        &$month = null,
        &$day = null,
        $tz = null
    ) {
        $now = new Carbon(null, $tz);

        $defaults = static::getJDateArray($now);

        $year = $year === null ? $defaults['year'] : $year;
        $month = $month === null ? $defaults['month'] : $month;
        $day = $day === null ? $defaults['day'] : $day;
    }

    /**
     * @param Carbon $carbon
     * @return array
     */
    private function getJDateArray($carbon)
    {
        return array_combine([
            'year',
            'month',
            'day',
            'hour',
            'minute',
            'second',
        ], explode('-', $carbon->jFormat('Y-n-j-G-i-s')));
    }

}
