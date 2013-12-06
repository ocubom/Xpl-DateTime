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

use Xpl\DateTime\TimeZone;

/**
 * TimeZoneTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class TimeZoneTest extends TestCase
{
    /**
     * Constructor test
     *
     * @param string $iso  ISO8601 text representation. Null for invalid value.
     * @param string $args Constructor arguments.
     *
     * @dataProvider provideTimezones
     */
    public function testConstructor($iso, $args)
    {
        // Check if arguments are valid or setup exception
        if (null === $iso) {
            // Configure expected exception
            $this->setExpectedException('\\Xpl\\DateTime\\Exception\\InvalidArgumentException');
        }

        // Create the object
        $timezone = new TimeZone($args);

        // Must implement __toString that returns the timezone representation
        $this->assertEquals($iso, "$timezone");
    }

    /**
     * Timezone arguments provider.
     *
     * @return array
     */
    public function provideTimezones()
    {
        return array(
            // By default, use PHP configuration value
            array(self::DEFAULT_TZ, null),
            // Valid timezones
            array('UTC'          , 'UTC'),
            array('Europe/Berlin', 'CET'),
            array('Europe/Madrid', 'Europe/Madrid'),
            // Copy constructor
            array('UTC'          , new \DateTimeZone('UTC')),
            array('Europe/Berlin', new \DateTimeZone('CET')),
            array('Europe/Madrid', new \DateTimeZone('Europe/Madrid')),
            array('UTC'          , new TimeZone('UTC')),
            array('Europe/Berlin', new TimeZone('CET')),
            array('Europe/Madrid', new TimeZone('Europe/Madrid')),
            array('Europe/Madrid', new TimeZone('Europe/Madrid')),
            array('Europe/Berlin', new \DateTime('CET')),
            // Invalid timezones
            array(null, 'Mars/Phobos'),
            array(null, 'Jupiter/Europa'),
            array(null, new \ArrayIterator(array())),
        );
    }
}
