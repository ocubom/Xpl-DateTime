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

/**
 * IntervalTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class IntervalTest extends TestCase
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
        self::$factory = new \ReflectionClass('\\Xpl\\DateTime\\Interval');
    }

    /**
     * Constructor test
     *
     * @param string   $iso     ISO8601 text representation
     * @param string   $args    Constructor arguments
     * @param DateTime $from    Interval start date
     * @param DateTime $till    Interval finish date
     * @param float    $seconds Duration in seconds (null if unlimited)
     *
     * @dataProvider provideIntervalSpecifications
     */
    public function testConstructor($iso, $args, $from, $till, $seconds)
    {
        // Check if arguments are valid
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('Xpl\\DateTime\\Exception\\Exception');
        }

        // Create object
        $interval = self::$factory->newInstanceArgs($args);
        // Must implement __toString that returns the ISO8601 representation
        $this->assertEquals($iso, "$interval");

        // "Copy" constructor
        $interval = new Interval($interval);
        // Must implement __toString that returns the ISO8601 representation
        $this->assertEquals($iso, "$interval");

        // Check from limit
        if (null === $from) {
            $this->assertFalse($interval->hasFromDate());
            $this->assertEquals(-INF, $interval->getFromTimestamp());
        } else {
            $this->assertTrue($interval->hasFromDate());
            $this->assertEquals((string) $from, (string) $interval->getFromDate());
            $this->assertEquals($from->getTimestamp(), $interval->getFromTimestamp());
        }

        // Check till limit
        if (null === $till) {
            $this->assertFalse($interval->hasTillDate());
            $this->assertEquals(-INF, $interval->getTillTimestamp());
        } else {
            $this->assertTrue($interval->hasTillDate());
            $this->assertEquals((string) $till, (string) $interval->getTillDate());
            $this->assertEquals($till->getTimestamp(), $interval->getTillTimestamp());
        }

        // Check duration
        if (null === $seconds) {
            $this->assertFalse($interval->isLimited());

            // Configure expected exception
            $this->setExpectedException('Xpl\\DateTime\\Exception\\LogicException');
        } else {
            $this->assertTrue($interval->isLimited());
        }

        $duration = $interval->getDuration();
        // Check seconds
        $this->assertEquals($seconds, $duration->getSeconds());
    }

    /**
     * Interval specifications provider
     *
     * @return array
     */
    public function provideIntervalSpecifications()
    {
        return array(

            // 0. Two DateTime
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array(new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC')),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 1. Two \DateTime
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array(new \DateTime('2000-01-01 00:00:00 UTC'), new \DateTime('2001-01-01 00:00:00 UTC')),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 2. Two date strings
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array('2000-01-01 00:00:00 UTC', '2001-01-01 00:00:00 UTC'),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 3. DateTime + Duration
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array(new DateTime('2000-01-01 00:00:00 UTC'), new Duration('P1Y')),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 4. Datetime string + duration string
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array('2000-01-01 00:00:00 UTC', 'P1Y'),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 5. Duration string + datetime string
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array('P1Y', '2001-01-01 00:00:00 UTC'),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 6. No from
            array(
                '-/2001-01-01T00:00:00+00:00',
                array(null, '2001-01-01 00:00:00 UTC'),
                null, new DateTime('2001-01-01 00:00:00 UTC'), null
            ),

            // 7. No till
            array(
                '2000-01-01T00:00:00+00:00/-',
                array('2000-01-01 00:00:00 UTC', null),
                new DateTime('2000-01-01 00:00:00 UTC'), null, null
            ),

            // 8. Spec constructor
            array(
                '2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00',
                array('2000-01-01T00:00:00+00:00/2001-01-01T00:00:00+00:00'),
                new DateTime('2000-01-01 00:00:00 UTC'), new DateTime('2001-01-01 00:00:00 UTC'), 31622400
            ),

            // 9. Spec constructor with null finish (second arg ignored)
            array(
                '2000-01-01T00:00:00+00:00/-',
                array('2000-01-01T00:00:00+00:00/-', '2001-01-01 00:00:00 UTC'),
                new DateTime('2000-01-01 00:00:00 UTC'), null, null
            ),

            // 10. Spec constructor with null start (second arg ignored)
            array(
                '-/2001-01-01T00:00:00+00:00',
                array('-/2001-01-01T00:00:00+00:00', '2010-01-01 00:00:00 UTC'),
                null, new DateTime('2001-01-01 00:00:00 UTC'), null
            ),

            // 11. Unlimited interval
            array(
                '-/-',
                array('-/-', '2010-01-01 00:00:00 UTC'),
                null, null, null
            ),

            // 12. Invalid spec
            array(
                null,
                array(new \DateTimeZone('UTC')),
                null, null, null
            ),
        );
    }
}
