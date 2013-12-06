<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * (c) Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime\Test;

/**
 * XPL DateTime Base Test Case.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    // Timezones used on tests
    const DEFAULT_TZ = 'Europe/Madrid';
    const PACIFIC_TZ = 'Pacific/Nauru';

    /**
     * Backup timezone
     *
     * @var string
     */
    static private $timezone;

    /**
     * Setup test environment
     */
    public static function setUpBeforeClass()
    {
        // Backup timezone and set a default known value
        self::$timezone = date_default_timezone_get();
        date_default_timezone_set(self::DEFAULT_TZ);
    }

    /**
     * Clean test environment.
     */
    public static function tearDownAfterClass()
    {
        // Restore backed timezone
        date_default_timezone_set(self::$timezone);
    }
}
