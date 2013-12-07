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
 * Interval
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class Interval implements IntervalInterface
{
    /**
     * Start date (from)
     *
     * @var DateTime
     */
    private $from;

    /**
     * Finish date (till)
     *
     * @var DateTime
     */
    private $till;

    /**
     * Constructor
     *
     * @param mixed $specfrom Date interval or date&time specification
     * @param mixed $spectill Date interval or date&time specification
     */
    public function __construct($specfrom = null, $spectill = null)
    {
        // Resolve arguments
        $from = Util::create(
            $specfrom,
            array('Interval', 'DateTime', 'DateInterval'),
            $specfrom // Use object as-is when conversion fails
        );
        $till = Util::create(
            $spectill,
            array('Interval', 'DateTime', 'DateInterval'),
            $spectill // Use object as-is when conversion fails
        );

        // Create based on resolved object types
        if ($from instanceof Interval) {
            // Copy constructor
            $this->setFromDate($from->from);
            $this->setTillDate($from->till);
        } elseif ($from instanceof \DateInterval) {
            // Duration and end (calculate start date)
            $date = new DateTime($till);
            $date->sub($from);

            $this->setFromDate($date);
            $this->setTillDate($till);
        } elseif ($till instanceof \DateInterval) {
            // Start and duration (calculate end date)
            $date = new DateTime($from);
            $date->add($till);

            $this->setFromDate($from);
            $this->setTillDate($date);
        } else {
            // Two date-times
            $this->setFromDate($from);
            $this->setTillDate($till);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \IntrevalInterface::contains()
     */
    public function contains($datetime)
    {
        // Convert argument into Datetime
        $datetime = new DateTime($datetime);

        // Check from (if any)
        if (null !== $this->from && $datetime < $this->from) {
            return false;
        }

        // Check till (if any)
        if (null !== $this->till && $datetime >= $this->till) {
            return false;
        }

        return true;
    }

    /**
     * Iterate the interval based on duration
     *
     * @param mixed $step    Step count or duration specification
     * @param int   $options Iterator options
     *
     * @return \RepeatingInterval
     */
    public function iterate($step, $options = 0)
    {
        return new RepeatingInterval($this, $step, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new RepeatingInterval($this, 'P1D');
    }

    /**
     * Check if from (start) date is set
     *
     * @return boolean
     */
    public function hasFromDate()
    {
        return null !== $this->from;
    }

    /**
     * From (start) date
     *
     * @return DateTime
     */
    public function getFromDate()
    {
        return $this->from;
    }

    /**
     * From (start) UNIX timestamp.
     *
     * May return -INF for unlimited intervals
     *
     * @return float
     */
    public function getFromTimestamp()
    {
        return null === $this->from ? -INF : $this->from->getTimestamp();
    }

    /**
     * From (start) date
     *
     * @param mixed $value Datetime, timestamp or strtotime formatted string
     *
     * @return Period
     */
    public function setFromDate($value)
    {
        $this->from = empty($value) ? null : new DateTime($value);

        return $this;
    }

    /**
     * Check if till (finish) date is set
     *
     * @return boolean
     */
    public function hasTillDate()
    {
        return null !== $this->till;
    }

    /**
     * Till (finish) date
     *
     * May return +INF for unlimited intervals
     *
     * @return DateTime
     */
    public function getTillDate()
    {
        return $this->till;
    }

    /**
     * Till (finish) UNIX timestamp
     *
     * @return float
     */
    public function getTillTimestamp()
    {
        return null === $this->till ? +INF : $this->till->getTimestamp();
    }

    /**
     * Till (finish) date
     *
     * @param mixed $value Datetime, timestamp or strtotime formatted string
     *
     * @return Period
     */
    public function setTillDate($value)
    {
        $this->till = empty($value) ? null : new DateTime($value);

        return $this;
    }

    /**
     * Has both from and till dates
     *
     * @return boolean True if the interval is limited
     */
    public function isLimited()
    {
        return null !== $this->from && null !== $this->till;
    }

    /**
     * Lack of from or till dates
     *
     * @return boolean True if the interval is unlimited
     */
    public function isUnlimited()
    {
        return null === $this->from || null === $this->till;
    }

    /**
     * Duration of the period
     *
     * @return Duration
     */
    public function getDuration()
    {
        if ($this->isUnlimited()) {
            throw new LogicException('Could not calculate duration of an unlimited interval');
        }

        return new Duration($this->from, $this->till);
    }

    /**
     * Converts the instance into a ISO8601 valid representation
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "%s/%s",
            $this->from ?: '-',
            $this->till ?: '-'
        );
    }
}
