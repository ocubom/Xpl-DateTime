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

/**
 * DurationTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DurationTest extends TestCase
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
        self::$factory = new \ReflectionClass('\\Xpl\\DateTime\\Duration');
    }

    /**
     * ISO constructor
     *
     * @param string $iso   ISO8601 text representation. Null for invalid value.
     * @param array  $args  Constructor arguments.
     * @param array  $units Duration in several units (seconds is mandatory).
     *
     * @dataProvider provideConstructorArgs
     */
    public function testConstructor($iso, $args, $units = null)
    {
        // Check if arguments are valid
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\Exception');
        }

        // Create object
        $duration = self::$factory->newInstanceArgs($args);

        // Must implement __toString that returns the ISO8601 representation
        $this->assertEquals($iso, "$duration");

        // Check stored value
        if (null === $units) {
            // Check if is no accurate
            $this->assertFalse($duration->isAccurate());
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\LogicException');
            // Set "empty" array
            $units = array('seconds' => 'invalid');
        } else {
            // Check if is accurate
            $this->assertTrue($duration->isAccurate());
        }

        // Check value in different units
        foreach ($units as $unit => $value) {
            $method = sprintf('get%s', ucfirst($unit));
            $this->assertEquals($value, $duration->$method(), $unit);
        }
        $this->assertEquals($units['seconds'] < 0, $duration->isInverted());
    }

    /**
     * Date string constructor
     *
     * @param string $iso   ISO8601 text representation. Null for invalid value.
     * @param array  $args  Constructor arguments.
     * @param array  $units Duration in several units (seconds is mandatory).
     *
     * @dataProvider provideFactoryArgs
     */
    public function testFactory($iso, $args, $units = null)
    {
        // Check if arguments are valid
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\Exception');
        }

        // Create object
        $duration = Duration::createFromDateString($args);

        // Must implement __toString that returns the ISO8601 representation
        $this->assertEquals($iso, "$duration");

        // Check stored value
        if (null === $units) {
            // Check if is no accurate
            $this->assertFalse($duration->isAccurate());
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\LogicException');
            // Set "empty" array
            $units = array('seconds' => 'invalid');
        } else {
            // Check if is accurate
            $this->assertTrue($duration->isAccurate());
        }

        // Check value in different units
        foreach ($units as $unit => $value) {
            $method = sprintf('get%s', ucfirst($unit));
            $this->assertEquals($value, $duration->$method(), $unit);
        }
        $this->assertEquals($units['seconds'] < 0, $duration->isInverted());
    }

    /**
     * Check manual modification on public members
     */
    public function testManualModifications()
    {
        // Create a duration
        $duration = new Duration('1 week');
        $this->assertEquals('P0Y0M7DT0H0M0S', "$duration");
        $this->assertEquals(604800, $duration->getSeconds());

        // Modify few seconds
        $duration->s -= 600;
        $this->assertEquals('P0Y0M6DT23H50M0S', "$duration");
        $this->assertEquals(604200, $duration->getSeconds());

        // Restore seconds in minutes
        $duration->i += 10;
        $this->assertEquals('P0Y0M7DT0H0M0S', "$duration");
        $this->assertEquals(604800, $duration->getSeconds());

        // "Do nothing" modifications
        $duration->s -= 600;
        $duration->i += 10;
        $this->assertEquals('P0Y0M7DT0H0M0S', "$duration");
        $this->assertEquals(604800, $duration->getSeconds());

        // Convert to non-accurate
        $duration->m = 1;
        // Configure expected exception
        $this->assertEquals('P0Y1M7DT0H0M0S', "$duration");
        $this->setExpectedException('\\Xpl\\DateTime\\Exception\\LogicException');
        $duration->getWeeks();
    }

    /**
     * Constructor arguments provider
     *
     * @return array
     */
    public function provideConstructorArgs()
    {
        return array(

            // 0. Full specification
            array(
                'P1Y2M3DT4H5M6S',
                array('P1Y2M3DT4H5M6S'),
            ),

            // 1. Weeks
            array(
                'P0Y0M28DT0H0M0S',
                array('P4W'),
                array( 'seconds' => 2419200 )
            ),

            // 2. Lot of seconds
            array(
                'P0Y0M28DT0H0M0S',
                array('PT2419200S'),
                array( 'seconds' => 2419200, )
            ),

            // 3. Interval + date
            array(
                'P1Y0M15DT1H0M0S',
                array('P1Y2WT25H', new \DateTime('2000-01-01 00:00:00 UTC')),
                array( 'seconds' => 32922000 )
            ),

            // 4. 2 dates with different timezones
            array(
                'P1Y0M0DT0H0M0S',
                array(new \DateTime('2000-01-01 00:00:00 UTC'), new \DateTime('2001-01-01 01:00:00 CET')),
                array(
                    'seconds' => 31622400,
                    'minutes' =>   527040,
                    'hours'   =>     8784,
                    'days'    =>      366,
                    'weeks'   =>      366/7,
                )
            ),

            // 5. Inverted
            array(
                '-P1Y0M0DT0H0M0S',
                array(new DateTime('2001-01-01 00:00:00 UTC'), new DateTime('2000-01-01 00:00:00.5 UTC')),
                array(
                    'seconds' => -31622400,
                    'minutes' => -  527040,
                    'hours'   => -    8784,
                    'days'    => -     366,
                    'weeks'   => -     366/7,
                )
            ),

            // 6. Relative parts
            array(
                'P1Y1M15DT0H0M0S',
                array('1 year + 2 weeks + 1 month + 23 hours + 3600 seconds')
            ),

            // 7. Negative ISO
            array(
                '-P0Y0M28DT0H0M0S',
                array('-PT2419200S'),
                array( 'seconds' => -2419200 )
            ),

            // 8. Empty constructor
            array(
                'P0Y0M0DT0H0M0S',
                array(''),
                array( 'seconds' => 0 )
            ),

            // 9. Copy constructor
            array(
                'P0Y0M28DT0H0M0S',
                array(new Duration('P4W')),
                array( 'seconds' => 2419200 )
            ),

            // 10. Convert constructor
            array(
                'P0Y0M28DT0H0M0S',
                array(new \DateInterval('P4W')),
                array( 'seconds' => 2419200 )
            ),

            // 11. Invalid arguments
            array(null, array('InvalidSpec')),

            // 12. Invalid arguments
            array(null, array('P1W', 'InvalidDate')),

        );
    }

    /**
     * Factory arguments provider
     *
     * @return array
     */
    public function provideFactoryArgs()
    {
        return array(

            // 0. Non-accurate date string
            array(
                'P1Y1M15DT0H0M0S',
                '1 year + 2 weeks + 1 month + 23 hours + 3600 seconds',
                null
            ),

            // 1. Accurate date string
            array(
                'P0Y0M15DT0H0M0S',
                '23 hours + 2 weeks + 3600 seconds',
                array( 'seconds' => 1296000 )
            ),

        );
    }
}
