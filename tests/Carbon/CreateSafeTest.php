<?php

/*
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Carbon;

use Mdaliyan\Carbon;
use Mdaliyan\Exceptions\InvalidDateException;
use Tests\AbstractTestCase;

class CreateSafeTest extends AbstractTestCase
{
    public function testInvalidDateExceptionProperties()
    {
        $e = new InvalidDateException('day', 'foo');
        $this->assertSame('day', $e->getField());
        $this->assertSame('foo', $e->getValue());
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage second : -1 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForSecondLowerThanZero()
    {
        Carbon::createSafe(null, null, null, null, null, -1);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage second : 60 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForSecondGreaterThan59()
    {
        Carbon::createSafe(null, null, null, null, null, 60);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage minute : -1 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForMinuteLowerThanZero()
    {
        Carbon::createSafe(null, null, null, null, -1);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage minute : 60 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForMinuteGreaterThan59()
    {
        Carbon::createSafe(null, null, null, null, 60, 25);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage hour : -6 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForHourLowerThanZero()
    {
        Carbon::createSafe(null, null, null, -6);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage hour : 25 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForHourGreaterThan24()
    {
        Carbon::createSafe(null, null, null, 25, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage day : -5 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForDayLowerThanZero()
    {
        Carbon::createSafe(null, null, -5);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage day : 32 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForDayGreaterThan31()
    {
        Carbon::createSafe(null, null, 32, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage month : -4 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForMonthLowerThanZero()
    {
        Carbon::createSafe(null, -4);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage month : 13 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForMonthGreaterThan12()
    {
        Carbon::createSafe(null, 13, 5, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage year : -5 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForYearLowerThanZero()
    {
        Carbon::createSafe(-5);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage year : 10000 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForYearGreaterThan12()
    {
        Carbon::createSafe(10000, 12, 5, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage day : 31 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForInvalidDayInShortMonth()
    {
        // 30 days in April
        Carbon::createSafe(2016, 4, 31, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage day : 30 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForInvalidDayForFebruaryInLeapYear()
    {
        // 29 days in February for a leap year
        $this->assertTrue(Carbon::create(2016, 2)->isLeapYear());
        Carbon::createSafe(2016, 2, 30, 17, 16, 15);
    }

    public function testCreateSafePassesForFebruaryInLeapYear()
    {
        // 29 days in February for a leap year
        Carbon::createSafe(2016, 2, 29, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage day : 29 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForInvalidDayForFebruaryInNonLeapYear()
    {
        // 28 days in February for a non-leap year
        $this->assertFalse(Carbon::create(2015, 2)->isLeapYear());
        Carbon::createSafe(2015, 2, 29, 17, 16, 15);
    }

    /**
     * @expectedException \Mdaliyan\Exceptions\InvalidDateException
     * @expectedExceptionMessage second : 15.1 is not a valid value.
     */
    public function testCreateSafeThrowsExceptionForWithNonIntegerValue()
    {
        Carbon::createSafe(2015, 2, 10, 17, 16, 15.1);
    }

    public function testCreateSafePassesForFebruaryInNonLeapYear()
    {
        // 28 days in February for a non-leap year
        Carbon::createSafe(2015, 2, 28, 17, 16, 15);
    }

    public function testCreateSafePasses()
    {
        $sd = Carbon::createSafe(2015, 2, 15, 17, 16, 15);
        $d = Carbon::create(2015, 2, 15, 17, 16, 15);
        $this->assertEquals($d, $sd);
        $this->assertCarbon($sd, 2015, 2, 15, 17, 16, 15);
    }
}
