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
use Xpl\DateTime\TimeZone;

/**
 * DateTimeTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DateTimeTest extends TestCase
{
    /**
     * Constructor test
     *
     * @param string $iso      ISO8601 text representation. Null for invalid value.
     * @param string $args     Constructor arguments
     * @param string $timezone Timezone name
     *
     * @dataProvider provideConstructorArgs
     */
    public function testConstructor($iso, $args, $timezone = null)
    {
        // Obtain class factory
        $class = $this->getClass();

        // Check if arguments are valid or setup exception
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\InvalidArgumentException');
        }

        // Create object
        $datetime = $class->newInstanceArgs($args);

        // Must implement __toString that returns the ISO8601 representation
        $this->assertEquals($iso, "$datetime");
        // Must manage timezones
        $this->assertEquals($timezone, $datetime->getTimezone()->getName());
    }

    /**
     * Factory (createFromFormat) test
     *
     * @param string $iso  ISO8601 text representation. Null for invalid value.
     * @param string $args Factory args.
     *
     * @depends testConstructor
     * @dataProvider provideFactoryArgs
     */
    public function testFactory($iso, $args)
    {
        // Obtain class factory
        $class = $this->getClass();

        // Check if arguments are valid or setup exception
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\InvalidArgumentException');
        }

        // createFromFormat
        $datetime = $class->getMethod('createFromFormat')->invokeArgs(null, $args);
        $this->assertEquals($iso, "$datetime");
    }

    /**
     * Check format on diferent timezone
     */
    public function testFormat()
    {
        // Obtain class factory
        $class = $this->getClass();

        // Create reference timestamp and DateTime object
        $timestamp = time();
        $datetime  = $class->newInstance(date(DateTime::PORTABLE, $timestamp));

        // Check on default timezone
        $this->assertEquals(date('c', $timestamp), "$datetime");
        $this->assertEquals(date(DateTime::PORTABLE, $timestamp), $datetime->format(DateTime::PORTABLE));
        // Check both on UTC
        $this->assertEquals(gmdate('c', $timestamp), $datetime->format('c', 'UTC'));
        $this->assertEquals(gmdate(DateTime::PORTABLE, $timestamp), $datetime->format(DateTime::PORTABLE, 'UTC'));
    }

    /**
     * Check that the same call to methods produces the same result.
     *
     * @param string $method Method name
     * @param array  $args   Method arguments
     *
     * @depends testConstructor
     * @dataProvider provideMethods
     */
    public function testMethods($method, $args)
    {
        // Obtain class factory
        $class = $this->getClass();

        // Create now object as reference
        $ref = $class->newInstance();
        // Copy reference on both SPL and XPL objects
        $xpl = $class->newInstance($ref);
        $spl = new \DateTime($ref->format(DateTime::PORTABLE));

        // Check that both are the same
        $this->assertEquals($spl->format(DateTime::PORTABLE), $xpl->format(DateTime::PORTABLE));

        // Perform operation
        $xplnew = call_user_func_array(array($spl, $method), $args);
        $splnew = call_user_func_array(array($xpl, $method), $args);

        // Check results
        $this->assertEquals(
            $splnew->format(DateTime::PORTABLE),
            $xplnew->format(DateTime::PORTABLE)
        );
        $this->assertEquals(
            $splnew->getOffset(),
            $splnew->getOffset()
        );

        // Check that diff is zero
        $diff = $xplnew->diff($splnew);
        $this->assertEquals('P0Y0M0DT0H0M0S', $diff->format('P%yY%mM%dDT%hH%iM%sS'));
    }

    /**
     * Constructor arguments provider
     *
     * Adapted from PHP documentation examples.
     * http://php.net/manual/datetime.construct.php#example-627
     *
     * @return array
     */
    public function provideConstructorArgs()
    {
        // Obtain class factory
        $class = $this->getClass();

        // Obtain today on default and alternative timezones
        $default = new \DateTime('today', new \DateTimeZone(self::DEFAULT_TZ));
        $pacific = new \DateTime('today', new \DateTimeZone(self::PACIFIC_TZ));

        return array(

            // 0. Specified date/time in your computer's time zone.
            array(
                '2000-01-01T00:00:00+01:00',
                array('2000-01-01'),
                self::DEFAULT_TZ,
            ),

            // 1. Specified date/time in the specified time zone.
            array(
                '2000-01-01T00:00:00+12:00',
                array('2000-01-01', new TimeZone(self::PACIFIC_TZ)),
                self::PACIFIC_TZ,
            ),

            // 2. Same as [1] but using string for timezone.
            array(
                '2000-01-01T00:00:00+12:00',
                array('2000-01-01', self::PACIFIC_TZ),
                self::PACIFIC_TZ,
            ),

            // 3. Today date/time in your computer's time zone.
            array(
                $default->format(DateTime::RFC3339),
                array('today'),
                self::DEFAULT_TZ,
            ),

            // 4. Today date/time in the specified time zone.
            array(
                $pacific->format(DateTime::RFC3339),
                array('today', self::PACIFIC_TZ),
                self::PACIFIC_TZ,
            ),

            // 5. Using a UNIX timestamp. Notice the result must be in UTC.
            array(
                '2000-01-01T00:00:00+00:00',
                array('@946684800', self::PACIFIC_TZ),
                '+00:00',
            ),

            // 6. Same as [6] but with numeric timestamp
            array(
                '2000-01-01T00:00:00+00:00',
                array(946684800, self::PACIFIC_TZ),
                'UTC',
            ),

            // 7. Non-existent values roll over.
            array(
                '2000-03-01T00:00:00+01:00',
                array('2000-02-30'),
                self::DEFAULT_TZ,
            ),

            // 8. Convert constructor. Should ignore timezone
            array(
                $default->format(DateTime::RFC3339),
                array($default, self::PACIFIC_TZ),
                self::DEFAULT_TZ,
            ),

            // 9. Copy constructor. Should ignore timezone
            array(
                $default->format(DateTime::RFC3339),
                array($class->newInstance($default), self::PACIFIC_TZ),
                self::DEFAULT_TZ,
            ),

            // 10. Invalid date
            array(
                null,
                array('InvalidDateString')
            ),

            // 11. Invalid types
            array(
                null,
                array(new \DateTimeZone('UTC'))
            ),

        );
    }

    /**
     * Factory arguments provider
     *
     * @return array
     */
    public function provideFactoryArgs()
    {
        // Reference timestamp
        $timestamp = time();

        return array(
            // Valid formats
            array(
                date(DateTime::RFC3339, $timestamp),
                array(\DateTime::ATOM, date(\DateTime::ATOM, $timestamp)),
            ),
            array(
                date(DateTime::RFC3339, $timestamp),
                array(\DateTime::RFC3339, date(\DateTime::RFC3339, $timestamp)),
            ),
            array(
                date(DateTime::RFC3339, $timestamp),
                array(\DateTime::W3C, date(\DateTime::W3C, $timestamp)),
            ),
            // Invalid format
            array(
                null,
                array('InvalidFormat', 'InvalidDate'),
            ),
        );
    }

    /**
     * Method provider
     *
     * @return array
     */
    public function provideMethods()
    {
        return array(
            array('add',         array(new \DateInterval('P1M'))),
            array('sub',         array(new \DateInterval('P1M'))),
            array('modify',      array('-1 day')),
            array('setDate',     array(2014, 3, 2)),
            array('setISODate',  array(2014, 3, 2)),
            array('setTime',     array(14, 3, 2)),
            array('setTimezone', array(new TimeZone(self::PACIFIC_TZ))),
        );
    }

    /**
     * Returns reflected class to test
     *
     * @return \ReflectionClass
     */
    protected function getClass()
    {
        // Cache instance on first call
        static $class = null;
        if (null === $class) {
            $class = new \ReflectionClass('\\Xpl\\DateTime\\DateTime');
        }

        return $class;
    }
}
