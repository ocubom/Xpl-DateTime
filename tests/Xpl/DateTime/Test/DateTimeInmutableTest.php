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

use Xpl\DateTime\DateTimeInmutable as DateTime;
use Xpl\DateTime\TimeZone;

/**
 * DateTimeTest
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DateTimeInmutableTest extends DateTimeTest
{
    /**
     * Check that the same call to methods produces the same result.
     *
     * @param string $method Method name
     * @param array  $args   Method arguments
     *
     * @dataProvider provideMethods
     */
    public function testMethods($method, $args)
    {
        // Obtain class factory
        $class = $this->getClass();

        // Perform DateTime check
        parent::testMethods($method, $args);

        // Create now object as reference
        $ref = $class->newInstance();
        // Copy reference on both SPL and XPL objects
        $xpl = $class->newInstance($ref);

        // Check that both are the same
        $this->assertEquals($ref->format(DateTime::PORTABLE), $xpl->format(DateTime::PORTABLE));

        // Perform operation
        call_user_func_array(array($xpl, $method), $args);

        // Check inmutable
        $this->assertEquals(
            $ref->format(DateTime::PORTABLE),
            $xpl->format(DateTime::PORTABLE),
            'Inmutable check'
        );
        $this->assertEquals(
            $ref->getOffset(),
            $xpl->getOffset(),
            'Inmutable check'
        );

        // Check that diff is zero
        $diff = $xpl->diff($ref);
        $this->assertEquals(
            'P0Y0M0DT0H0M0S',
            $diff->format('P%yY%mM%dDT%hH%iM%sS'),
            'Inmutable check'
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
            $class = new \ReflectionClass('\\Xpl\\DateTime\\DateTimeInmutable');
        }

        return $class;
    }
}
