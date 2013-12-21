<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime;

use Xpl\DateTime\Exception\InvalidArgumentException;

/**
 * TimeZone
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class TimeZone extends \DateTimeZone
{
    /**
     * Constructor.
     *
     * @param mixed $timezone Time zone specification
     */
    public function __construct($timezone = null)
    {
        try {
            // Create based on argument types
            if (empty($timezone)) {
                // Use default timezone
                parent::__construct(date_default_timezone_get());
            } elseif ($timezone instanceof \DateTimeZone) {
                // Copy/convert constructor
                parent::__construct($timezone->getName());
            } elseif (Util::isDateTime($timezone)) {
                // Copy DateTime zone
                parent::__construct($timezone->getTimeZone()->getName());
            } else {
                // Use timezonespec as-is
                parent::__construct(Util::toString($timezone, true));
            }
        } catch (\Xpl\DateTime\Exception\Exception $exception) {
            // Rethrow own exceptions
            throw $exception;
        } catch (\Exception $exception) {
            // Wrap exceptions
            throw new InvalidArgumentException(
                // Change constructor reference
                str_replace('DateTimeZone::__construct', __METHOD__, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Magic method used when treated like a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
