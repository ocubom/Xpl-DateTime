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
use Xpl\DateTime\Exception\LogicException;

/**
 * Duration
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class Duration extends \DateInterval
{
    // Internal format used for tranfers
    const ISO8601 = '%rP%yY%mM%dDT%hH%iM%sS';

    // Internal format used for tranfers
    const PORTABLE = 'P%yY%mM%dDT%hH%iM%sS';

    /**
     * Latest value
     *
     * @var string
     */
    private $checksum = null;

    /**
     * Total number of seconds in the interval
     *
     * @var float
     */
    private $seconds = false;

    /**
     * Constructor.
     *
     * @param mixed $duration Date interval or date&time specification
     * @param mixed $datetime Optinal reference date&time specification
     */
    public function __construct($duration, $datetime = null)
    {
        try {
            // Resolve specification
            $spec = Util::create(
                $duration,
                array('DateInterval', 'DateTime'),
                $duration // Use object as-is when conversion fails
            );

            // Create based on resolved object types
            if (empty($spec)) {
                // Create an empty interval
                parent::__construct('P0Y0M0DT0H0M0S');
            } elseif ($spec instanceof Duration) {
                // Copy constructor
                parent::__construct($spec->format(self::PORTABLE));
                $this->checksum = $spec->checksum;
                $this->invert   = $spec->invert;
                $this->seconds  = $spec->seconds;
            } elseif ($spec instanceof \DateTime) {
                // Two dates (second one may be null => now)
                $diff = date_diff($spec, new DateTime($datetime));

                // Copy diference
                parent::__construct($diff->format(self::PORTABLE));
                $this->invert = $diff->invert;
                // Calculate seconds count
                $this->seconds = 0
                    + $diff->days * 86400
                    + $diff->h    *  3600
                    + $diff->i    *    60
                    + $diff->s
                ;
            } elseif ($spec instanceof \DateInterval && !empty($datetime)) {
                // Reference datetime + interval
                $from = new DateTime($datetime);
                $till = clone $from;
                $till->add($spec);
                $diff = date_diff($from, $till);

                // Copy diference
                parent::__construct($diff->format(self::PORTABLE));
                $this->invert = $diff->invert;
                // Calculate seconds count
                $this->seconds = 0
                    + $diff->days * 86400
                    + $diff->h    *  3600
                    + $diff->i    *    60
                    + $diff->s
                ;
            } elseif ($spec instanceof \DateInterval) {
                // Convert constructor
                parent::__construct($spec->format(self::PORTABLE));
                $this->invert = $spec->invert;
            } else {
                // Fallback constructor
                parent::__construct(Util::toString($spec, true));
            }
        } catch (\Xpl\DateTime\Exception\Exception $exception) {
            // Rethrow own exceptions
            throw $exception;
        } catch (\Exception $exception) {
            // Wrap exceptions
            throw new InvalidArgumentException(
                // Change constructor reference
                str_replace('DateInterval::__construct', __METHOD__, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        // Clean instance
        $this->recalculate();
    }

    /**
     * Create duration from date string format
     *
     * @param string $spec Interval spec
     *
     * @return Duration
     *
     * @see \DateInterval::createFromDateString()
     */
    public static function createFromDateString($spec)
    {
        // Use copy constructor
        return new Duration(
            // Use parent method
            \DateInterval::createFromDateString(Util::toString($spec, true))
        );
    }

    /**
     * Create a date representation based on a given format.
     *
     * @param string $format Format accepted by \DateInterval::format
     *
     * @return string Formatted duration
     *
     * @see \DateInterval::format()
     */
    public function format($format)
    {
        $this->recalculate();

        return parent::format($format);
    }

    /**
     * Check if the duration is accurate so we can rely on it.
     *
     * The time duration is accurate when has no year or month components (both
     * have variable duration) or is related to a time interval (ISO 8601
     * section 4.4.1).
     *
     * This allows its unique conversion to all time units without meaning loss.
     *
     * @return boolean True
     */
    public function isAccurate()
    {
        return false !== $this->recalculate()->seconds;
    }

    /**
     * Is a negative interval period
     *
     * @return boolean
     */
    public function isInverted()
    {
        return 0 !== $this->recalculate()->invert;
    }

    /**
     * Weeks
     *
     * @return float
     */
    public function getWeeks()
    {
        return $this->getSeconds() / 604800.0; // 7 * 24 * 60 * 60
    }

    /**
     * Days
     *
     * @return float
     */
    public function getDays()
    {
        return $this->getSeconds() / 86400.0; // 24 * 60 * 60
    }

    /**
     * Hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->getSeconds() / 3600.0; // 60 * 60
    }

    /**
     * Minutes
     *
     * @return float
     */
    public function getMinutes()
    {
        return $this->getSeconds() / 60.0;
    }

    /**
     * Seconds
     *
     * @return float
     */
    public function getSeconds()
    {
        // This will recalculate instance
        if (!$this->isAccurate()) {
            throw new LogicException('Try to obtain absolute value for non-accurate duration');
        }

        return 0 === $this->invert ? $this->seconds : -$this->seconds;
    }

    /**
     * Converts the instance into a ISO8601 valid representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(self::ISO8601);
    }

    /**
     * Recalculate all values.
     *
     * Perform a cleanup of the instance. This is necesary to control changes
     * on public members.
     *
     * @return Duration
     */
    protected function recalculate()
    {
        // Changed?
        if (parent::format(static::ISO8601) === $this->checksum) {
            return $this;
        }

        // Redistribute time to assure correct moduli till days
        $this->s += $this->d * 86400;
        $this->s += $this->h *  3600;
        $this->s += $this->i *    60;

        // Days
        $this->d  = floor($this->s/86400);
        $this->s -= $this->d*86400;
        // Hours
        $this->h  = floor($this->s/3600);
        $this->s -= $this->h*3600;
        // Minutes
        $this->i  = floor($this->s/60);
        $this->s -= $this->i*60;

        // Recheck changes
        if (parent::format(static::ISO8601) === $this->checksum) {
            return $this;
        }

        // Update seconds count
        $this->seconds = $this->recalculateSeconds();

        // Clean invert
        if (false !== $this->seconds) {
            // Apply invert
            if (0 !== $this->invert) {
                $this->seconds *= -1;
            }

            // Update invert
            if ($this->seconds < 0) {
                $this->invert  = 1;
                $this->seconds = abs($this->seconds);
            } else {
                $this->invert = 0;
            }
        }

        // Update checksum
        $this->checksum = parent::format(static::ISO8601);

        return $this;
    }

    /**
     * Recalculate seconds
     *
     * @return float Total number of seconds or false if not accurate
     */
    private function recalculateSeconds()
    {
        // Update accurate count of seconds (year/month not used or no change)
        if (0 == $this->y && 0 == $this->m) {
            // No year/month
            return 0
                + $this->d * 86400
                + $this->h *  3600
                + $this->i *    60
                + $this->s
            ;
        } elseif (false === $this->seconds) {
            // Not accurate count, can not update
            return false;
        }

        // Obtain changes
        $changes = $this->recalculateChanges();
        // Changes on year/month => Not accurate count, can not update
        if (0 != $changes->y || 0 != $changes->m) {
            return false;
        }

        // Return the update seconds count
        return $this->seconds
            + $changes->d * 86400
            + $changes->h *  3600
            + $changes->i *    60
            + $changes->s
        ;
    }

    /**
     * Obtain the changes with checksum
     *
     * @return \DateInterval
     */
    private function recalculateChanges()
    {
        if (null === $this->checksum) {
            // First run => no changes
            return new \DateInterval('P0Y');
        }

        // Calculate changes
        $changes = new \DateInterval($this->checksum);
        $changes->y -= $this->y;
        $changes->m -= $this->m;
        $changes->d -= $this->d;
        $changes->h -= $this->h;
        $changes->i -= $this->i;
        $changes->s -= $this->s;

        return $changes;
    }
}
