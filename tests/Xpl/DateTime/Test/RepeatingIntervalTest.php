<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime\Test;

use Xpl\DateTime\DateTime;
use Xpl\DateTime\Duration;
use Xpl\DateTime\Interval;
use Xpl\DateTime\RepeatingInterval;
use Xpl\DateTime\TimeZone;

/**
 * RepeatingIntervalTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class RepeatingIntervalTest extends TestCase
{
    /**
     * Class reflected
     *
     * @var \ReflectionClass
     */
    static private $factory;

    /**
     * Setup test environment
     */
    public static function setUpBeforeClass()
    {
        // Call parent
        parent::setUpBeforeClass();

        // Obtain reflected class
        self::$factory = new \ReflectionClass('\\Xpl\\DateTime\\RepeatingInterval');
    }

    /**
     * Constructor test
     *
     * @param string   $args   Constructor arguments
     * @param array    $result ISO8601 text representation of dates
     * @param Duration $step   Iteration step duration
     *
     * @dataProvider provideValidConstructorArgs
     */
    public function testIterator($args, $result, $step)
    {
        // Create object
        $interval = self::$factory->newInstanceArgs($args);

        // Custom iteration
        $dates = array();
        $prev  = null;
        foreach ($interval as $key => $date) {
            // Check the type
            $this->assertInstanceOf('\\Xpl\\DateTime\\DateTime', $date);

            // Store value
            $dates[] = (string) $date;

            // Check intervals
            if (null !== $prev) {
                // Obtain Duration
                $duration = new Duration($prev, $date);

                // One day between points
                $this->assertEquals($step->getSeconds(), $duration->getSeconds());
            }
        }

        $this->assertEquals($result, $dates);
    }

    /**
     * Valid constructor arguments
     *
     * @return array
     */
    public function provideValidConstructorArgs()
    {
        return array(

            // 0. Interval + step duration
            array(
                array(new Interval('2000-01-01 00:00:00 Europe/Madrid', '2000-01-07 00:00:00 Europe/Madrid'), 'P1D'),
                array(
                    '2000-01-01T00:00:00+01:00',
                    '2000-01-02T00:00:00+01:00',
                    '2000-01-03T00:00:00+01:00',
                    '2000-01-04T00:00:00+01:00',
                    '2000-01-05T00:00:00+01:00',
                    '2000-01-06T00:00:00+01:00',
                ),
                'P1D',
            ),

            // 1. date + duration + date (\DatePeriod)
            array(
                array('2000-01-01 00:00:00', 'P1D', '2000-01-07 00:00:00'),
                array(
                    '2000-01-01T00:00:00+01:00',
                    '2000-01-02T00:00:00+01:00',
                    '2000-01-03T00:00:00+01:00',
                    '2000-01-04T00:00:00+01:00',
                    '2000-01-05T00:00:00+01:00',
                    '2000-01-06T00:00:00+01:00',
                ),
                'P1D',
            ),

            // 2. date + duration + recurrences (one less than days)
            array(
                array('2000-01-01 00:00:00', 'P1D', 6),
                array(
                    '2000-01-01T00:00:00+01:00',
                    '2000-01-02T00:00:00+01:00',
                    '2000-01-03T00:00:00+01:00',
                    '2000-01-04T00:00:00+01:00',
                    '2000-01-05T00:00:00+01:00',
                    '2000-01-06T00:00:00+01:00',
                ),
                'P1D',
            ),

            // 3. date + duration + recurrences + exclude start
            array(
                array('2000-01-01 00:00:00', 'P1D', 6, RepeatingInterval::EXCLUDE_START_DATE),
                array(
                    '2000-01-02T00:00:00+01:00',
                    '2000-01-03T00:00:00+01:00',
                    '2000-01-04T00:00:00+01:00',
                    '2000-01-05T00:00:00+01:00',
                    '2000-01-06T00:00:00+01:00',
                ),
                'P1D',
            ),

        );
    }
}
