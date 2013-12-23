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
 * Representation of date and time.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DateTime extends \DateTime implements DateTimeInterface
{
    // Internal format used for tranfers
    const PORTABLE = "Y-m-d\TH:i:s.u e";

    /**
     * Constructor.
     *
     * @param mixed $datespec Date&time specification
     * @param mixed $timezone Time zone specification
     *
     * @see \DateTime::__construct()
     */
    public function __construct($datespec = null, $timezone = null)
    {
        try {
            // Create based on argument types
            if (empty($datespec)) {
                // Use "now" on current configured timezone
                parent::__construct(date(self::PORTABLE));
            } elseif (Util::isDateTime($datespec)) {
                // Copy/convert constructor
                parent::__construct(
                    $datespec->format(self::PORTABLE),
                    $datespec->getTimezone() // Use instance timezone
                );
            } elseif (is_numeric($datespec)) {
                // UNIX timestamp
                parent::__construct(
                    gmdate(self::PORTABLE, 0 + $datespec),
                    new \DateTimeZone('UTC') // Timestamp always on UTC
                );
            } else {
                // Fallback constructor
                parent::__construct(
                    Util::toString($datespec, true),
                    new TimeZone($timezone) // Given timezone as object
                );
            }
        } catch (\Xpl\DateTime\Exception\Exception $exception) {
            // Rethrow own exceptions
            throw $exception;
        } catch (\Exception $exception) {
            // Wrap exceptions
            throw new InvalidArgumentException(
                // Change constructor reference
                str_replace('DateTime::__construct', __METHOD__, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Create a new DateTime object formatted according to the specified format.
     *
     * @param string $format   Format of the $datetime parameter
     * @param string $datetime Date&time specification
     * @param mixed  $timezone Time zone specification
     *
     * @return DateTime
     *
     * @see \DateTime::createFromFormat()
     */
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        // Use parent method
        $date = parent::createFromFormat(
            Util::toString($format, true),
            Util::toString($datetime, true),
            new TimeZone($timezone) // Given timezone as object
        );

        // Convert error into exception
        if (false === $date) {
            throw new InvalidArgumentException(sprintf('Invalid format "%s" for "%s".', $format, $datetime));
        }

        // Use copy constructor
        return new static($date);
    }

    /**
     * Adds the specified interval to the DateTime.
     *
     * @param mixed $interval Interval specification
     *
     * @return DateTime
     *
     * @see \DateTime::add()
     */
    public function add($interval)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not add %2$s to %1$s',
            // Convert argument into interval
            $interval instanceof \DateInterval ? $interval : new Duration($interval)
        );
    }

    /**
     * Difference between two DateTime objects
     *
     * @param mixed $datetime Date&time specification
     * @param bool  $absolute Should the interval be forced to be positive?
     *
     * @return \DateInterval
     *
     * @see \DateTime::diff()
     */
    public function diff($datetime, $absolute = false)
    {
        return new Duration(
            $this->callParent(
                // Name of the parent method
                __FUNCTION__,
                // Exception message
                'Could not diff %1$s and %2$s',
                // Clean arguments for parent call
                Util::isDateTime($datetime) ? $datetime : new DateTime($datetime),
                $absolute
            )
        );
    }

    /**
     * Date formatted according to given format.
     *
     * @param string $format   Format accepted by date().
     * @param mixed  $timezone Optional timezone for format
     *
     * @return string Formatted date
     *
     * @see \DateTime::format()
     */
    public function format($format, $timezone = null)
    {
        // Use myself as default
        $date = $this;
        // Request other timezone
        if (!empty($timezone)) {
            // Format a clone in the desired timezone
            $date = clone $this;
            $date = $date->setTimezone($timezone);
        }

        // Use parent format method
        $result = date_format($date, Util::toString($format));

        // Check returned value
        if (false === $result) {
            // @codeCoverageIgnoreStart
            // Format message and throw exception
            throw new InvalidArgumentException(sprintf('Could not format %s with %s', $this, $format));
            // @codeCoverageIgnoreEnd
        }

        // Return the value
        return $result;
    }

    /**
     * Alters timestamp
     *
     * @param mixed $modify A date&time string
     *
     * @return DateTime
     *
     * @see \DateTime::modify()
     */
    public function modify($modify)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not modify %1$s with %2$s',
            // Clean arguments for parent call
            Util::toString($modify)
        );
    }

    /**
     * Substracts the specified interval to the DateTime.
     *
     * @param mixed $interval Interval specification
     *
     * @return DateTime
     *
     * @see \DateTime::sub()
     */
    public function sub($interval)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not substract %2$s to %1$s',
            // Clean arguments for parent call
            $interval instanceof \DateInterval ? $interval : new Duration($interval)
        );
    }

    /**
     * Resets the current date to a different one.
     *
     * @param mixed $year  Year of the date
     * @param mixed $month Month of the date
     * @param mixed $day   Day of the date
     *
     * @return DateTime
     *
     * @see \DateTime::setDate()
     */
    public function setDate($year = null, $month = 1, $day = 1)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not set %2$s as timezone for %1$s',
            // Clean arguments for parent call
            empty($year)  ? date('Y') : $year,
            empty($month) ? 1 : $month,
            empty($day)   ? 1 : $day
        );
    }

    /**
     * Resets the current date according to the ISO 8601 standard
     *
     * @param mixed $year Year of the date
     * @param mixed $week Week of the date
     * @param mixed $day  Day of the date
     *
     * @return DateTime
     *
     * @see \DateTime::setISODate()
     */
    public function setISODate($year = null, $week = 1, $day = 1)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not set %2$s as timezone for %1$s',
            // Clean arguments for parent call
            empty($year) ? date('Y') : $year,
            empty($week) ? 1 : $week,
            empty($day)  ? 1 : $day
        );
    }

    /**
     * Timezone offset in seconds
     *
     * @return int
     *
     * @see \DateTime::getOffset()
     */
    public function getOffset()
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not retrieve timezone offset from %1$s'
        );
    }

    /**
     * Resets the current time to a different one.
     *
     * @param mixed $hour   Hour of the time
     * @param mixed $minute Minute of the time
     * @param mixed $second Second of the time
     *
     * @return DateTime
     *
     * @see \DateTime::setTime()
     */
    public function setTime($hour = 0, $minute = 0, $second = 0)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not set %2$s as timezone for %1$s',
            // Clean arguments for parent call
            empty($hour)   ? 0 : $hour,
            empty($minute) ? 0 : $minute,
            empty($second) ? 0 : $second
        );
    }

    /**
     * Timezone
     *
     * @return \DateTimeZone The Timezone
     *
     * @see \DateTime::getTimezone()
     */
    public function getTimezone()
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not retrieve timezone from %1$s'
        );
    }

    /**
     * Timezone
     *
     * @param mixed $timezone Timezone spec
     *
     * @return DateTime
     *
     * @see \DateTime::setTimezone()
     */
    public function setTimezone($timezone)
    {
        return $this->callParent(
            // Name of the parent method
            __FUNCTION__,
            // Exception message
            'Could not set %2$s as timezone for %1$s',
            // Clean arguments for parent call
            new TimeZone($timezone)
        );
    }

    /**
     * Magic method used when treated like a string
     *
     * @return string
     */
    public function __toString()
    {
        // ISO 8601 compilant (uses +HH:MM instead of +HHMM)
        return parent::format(self::RFC3339);
    }

    /**
     * Call parent method and converts any error into exceptions
     *
     * @param string $method    Method name
     * @param string $error     sprintf formatted error
     * @param mixed  $arguments Arguments for method (variable number)
     *
     * @return mixed The result of the parent
     *
     * @throws InvalidArgumentException Something fails
     */
    protected function callParent($method, $error, $arguments = null)
    {
        // Get all arguments (exclude $method)
        $arguments = array_slice(func_get_args(), 2);

        // Call parent
        $result = call_user_func_array(array('parent', $method), $arguments);

        // Check returned value
        if (false === $result) {
            // @codeCoverageIgnoreStart
            // Add error message, myself and method to the arguments
            array_unshift($arguments, $method);
            array_unshift($arguments, $this);
            array_unshift($arguments, $error);

            // Format message and throw exception
            throw new InvalidArgumentException(
                call_user_func_array('sprintf', $arguments)
            );
            // @codeCoverageIgnoreEnd
        }

        // Return the value
        return $result;
    }
}
