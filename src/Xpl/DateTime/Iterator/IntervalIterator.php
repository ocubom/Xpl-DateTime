<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime\Iterator;

use Xpl\DateTime\Datetime;
use Xpl\DateTime\Duration;
use Xpl\DateTime\IntervalInterface;
use Xpl\DateTime\Exception\InvalidArgumentException;

/**
 * Interval Iterator
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class IntervalIterator implements \Iterator
{
    // Exclude dates
    const EXCLUDE_FROM_DATE = \DatePeriod::EXCLUDE_START_DATE; // 1

    // Compatibility with \DatePeriod
    const EXCLUDE_START_DATE = \DatePeriod::EXCLUDE_START_DATE;

    /**
     * Interval to iterate
     *
     * @var IntervalInterface
     */
    private $interval;

    /**
     * Exclusion options
     *
     * @var integer
     */
    private $options;

    /**
     * Current position
     *
     * @var DateTime
     */
    private $current;

    /**
     * Constructor
     *
     * @param mixed $fromspec Iteration start specification (Interval, DateTime...)
     * @param mixed $duration Duration of each step
     * @param mixed $tillspec Iteration finish specification (Finish date or number of repetitions)
     * @param int   $options  Exclusion rules
     */

    /**
     * Constructor
     *
     * @param IntervalInterface $interval Interval to iterate
     * @param Duration          $duration Step duration of each iteration
     * @param integer           $options  Options
     */
    public function __construct(IntervalInterface $interval, Duration $duration, $options = 0)
    {
        $this->interval = $interval;
        $this->duration = $duration;
        $this->options  = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        // Return a copy of current date
        return clone $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        // No keys
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        // Increase current day the duration
        $this->current->add($this->duration);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        // Clone interval start date
        $this->current = new DateTime($this->interval->getFromDate());

        // Ignore start date
        if (self::EXCLUDE_FROM_DATE & $this->options) {
            $this->next();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        // Check if current date is still on the interval
        return $this->interval->contains($this->current);
    }
}
