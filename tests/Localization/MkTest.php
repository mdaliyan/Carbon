<?php

/*
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Localization;

use Mdaliyan\Carbon;
use Tests\AbstractTestCase;

class MkTest extends AbstractTestCase
{
    public function testDiffForHumansLocalizedInMacedonian()
    {
        Carbon::setLocale('mk');

        $scope = $this;
        $this->wrapWithNonDstDate(function () use ($scope) {
            $d = Carbon::now()->subSecond();
            $scope->assertSame('пред 1 секунда', $d->diffForHumans());

            $d = Carbon::now()->subSeconds(3);
            $scope->assertSame('пред 3 секунди', $d->diffForHumans());

            $d = Carbon::now()->subMinute();
            $scope->assertSame('пред 1 минута', $d->diffForHumans());

            $d = Carbon::now()->subMinutes(2);
            $scope->assertSame('пред 2 минути', $d->diffForHumans());

            $d = Carbon::now()->subHour();
            $scope->assertSame('пред 1 час', $d->diffForHumans());

            $d = Carbon::now()->subHours(2);
            $scope->assertSame('пред 2 часа', $d->diffForHumans());

            $d = Carbon::now()->subDay();
            $scope->assertSame('пред 1 ден', $d->diffForHumans());

            $d = Carbon::now()->subDays(2);
            $scope->assertSame('пред 2 дена', $d->diffForHumans());

            $d = Carbon::now()->subWeek();
            $scope->assertSame('пред 1 седмица', $d->diffForHumans());

            $d = Carbon::now()->subWeeks(2);
            $scope->assertSame('пред 2 седмици', $d->diffForHumans());

            $d = Carbon::now()->subMonth();
            $scope->assertSame('пред 1 месец', $d->diffForHumans());

            $d = Carbon::now()->subMonths(2);
            $scope->assertSame('пред 2 месеци', $d->diffForHumans());

            $d = Carbon::now()->subYear();
            $scope->assertSame('пред 1 година', $d->diffForHumans());

            $d = Carbon::now()->subYears(2);
            $scope->assertSame('пред 2 години', $d->diffForHumans());

            $d = Carbon::now()->addSecond();
            $scope->assertSame('1 секунда од сега', $d->diffForHumans());

            $d = Carbon::now()->addSecond();
            $d2 = Carbon::now();
            $scope->assertSame('по 1 секунда', $d->diffForHumans($d2));
            $scope->assertSame('пред 1 секунда', $d2->diffForHumans($d));

            $scope->assertSame('1 секунда', $d->diffForHumans($d2, true));
            $scope->assertSame('2 секунди', $d2->diffForHumans($d->addSecond(), true));
        });
    }
}
