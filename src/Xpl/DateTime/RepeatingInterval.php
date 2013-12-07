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
use Xpl\DateTime\Iterator\IntervalIterator;

/**
 * Repeating interval
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class RepeatingInterval implements IntervalInterface, \IteratorAggregate
{
    // Exclude dates
    const EXCLUDE_FROM_DATE = \DatePeriod::EXCLUDE_START_DATE; // 1
    // Compatibility with \DatePeriod
    const EXCLUDE_START_DATE = \DatePeriod::EXCLUDE_START_DATE; // 1

    /**
     * Interval to interate
     *
     * @var \IntervalInterface
     */
    private $interval;

    /**
     * Step duration
     *
     * @var Duration
     */
    private $step;

    /**
     * Number of steps in interval
     *
     * @var Integer
     */
    private $count;

    /**
     * Exclusion options
     *
     * @var integer
     */
    private $options;

    /**
     * Constructor
     *
     * @param mixed $spec    Iteration start specification (Interval, DateTime...)
     * @param mixed $step    Duration of each step
     * @param mixed $till    Iteration finish specification (Finish date or number of repetitions)
     * @param int   $options Exclusion rules
     */
    public function __construct($spec, $step = 'P1D', $till = null, $options = 0)
    {
        try {
            // Get all arguments
            $args = func_get_args();
            // Resolve argument
            $args[0] = self::resolveSpec($spec);

            // Create based on resolved object types
            if ($args[0] instanceof RepeatingInterval) {
                // Copy constructor
                $this->interval = $args[0]->interval;
                $this->step     = $args[0]->step;
                $this->count    = $args[0]->count;
                $this->options  = $args[0]->options;
            } elseif ($args[0] instanceof Interval && is_numeric($args[1])) {
                // new RepeatingInterval($interval, $count, $options)
                $this->interval = $args[0];
                $this->count    = 0 + $args[1];
                $this->options  = isset($args[2]) ? 0 + $args[2] : 0;

                // Calculate step duration
                $this->step = new Duration($this->getDuration()->getSeconds() / $this->count);
            } elseif ($args[0] instanceof Interval) {
                // new RepeatingInterval($interval, $step, $options)
                $this->interval = $args[0];
                $this->step     = new Duration($args[1]);
                $this->options  = isset($args[2]) ? 0 + $args[2] : 0;

                // Calculate step count
                $this->count = $this->getDuration()->getSeconds() / $this->step->getSeconds();
            } elseif ($args[0] instanceof \DateTime && is_numeric($args[2])) {
                // new RepeatingInterval($fromdate, $step, $count, $options)
                $this->step    = new Duration($args[1]);
                $this->count   = 0 + $args[2];
                $this->options = isset($args[3]) ? 0 + $args[3] : 0;

                // Create interval (will calculate finish date)
                $this->interval = new Interval($args[0], new Duration($this->step->getSeconds() * $this->count));
            } elseif ($args[0] instanceof \DateTime) {
                // new RepeatingInterval($fromdate, $step, $tilldate, $options)
                $this->interval = new Interval($args[0], $args[2]);
                $this->step     = new Duration($args[1]);
                $this->options  = isset($args[3]) ? 0 + $args[3] : 0;

                // Calculate step count
                $this->count = $this->getDuration()->getSeconds() / $this->step->getSeconds();
            } elseif (preg_match('@^R(\d+)?/([^/]+)/(P[^/]+)$@Uis', (string) $args[0], $matches)) {
                // new RepeatingInterval($iso, $options)
                $this->options = isset($args[1]) ? 0 + $args[1] : 0;
                // Matches
                $this->count = 0 + $matches[1];
                $this->step  = new Duration($matches[3]);

                // Create interval (will calculate finish date)
                $this->interval = new Interval($matches[2], $this->step);
            } else {
                // Unable to resolve definitions
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid repeating interval definition ("%s", "%s", "%s", "%s")',
                        $spec,
                        $step,
                        $till,
                        $options
                    )
                );
            }
        } catch (\Xpl\DateTime\Exception\Exception $exception) {
            // Rethrow own exceptions
            throw $exception;
        } catch (\Exception $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid repeating interval definition ("%s", "%s", "%s", "%s")',
                    $spec,
                    $step,
                    $till,
                    $options
                ),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \IntrevalInterface::contains()
     */
    public function contains($datetime)
    {
        return $this->interval->contains($datetime);
    }

    /**
     * {@inheritdoc}
     * {@see IntervalInterface::getFromDate()}
     */
    public function getFromDate()
    {
        return $this->interval->getFromDate();
    }

    /**
     * {@inheritdoc}
     * {@see IntervalInterface::getFromTimestamp()}
     */
    public function getFromTimestamp()
    {
        return $this->interval->getFromTimestamp();
    }

    /**
     * {@inheritdoc}
     * {@see IntervalInterface::getTillDate()}
     */
    public function getTillDate()
    {
        return $this->interval->getTillDate();
    }

    /**
     * {@inheritdoc}
     * {@see IntervalInterface::getTillTimestamp()}
     */
    public function getTillTimestamp()
    {
        return $this->interval->getTillTimestamp();
    }

    /**
     * {@inheritdoc}
     * {@see IntervalInterface::getDuration()}
     */
    public function getDuration()
    {
        return $this->interval->getDuration();
    }

    /**
     * {@inheritdoc}
     * {@see \IteratorAggregate::getDuration()}
     */
    public function getIterator()
    {
        return new IntervalIterator($this->interval, $this->step, $this->options);
    }

    /**
     * Number of steps
     *
     * @return integer
     */
    public function getStepCount()
    {
        return clone $this->count;
    }

    /**
     * Duration of an step
     *
     * @return Duration
     */
    public function getStepDuration()
    {
        return clone $this->step;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return sprintf(
            empty($this->count) ? 'R/%s/%s' : 'R%3$d/%1$s/%2$s',
            $this->getFromDate(),
            $this->step,
            $this->count
        );
    }

    /**
     * Try to convert spec into a valid object
     *
     * @param string $value Specification
     *
     * @return mixed An object generated with the value
     */
    private static function resolveSpec($value)
    {
        // Convert empty values into null
        if (empty($value)) {
            return null;
        }
        // Do not convert IntervalInterface or DateTime
        if ($value instanceof IntervalInterface || $value instanceof \DateTime) {
            return $value;
        }

        // Try to convert into a valid DateTime
        try {
            return new DateTime($value);
        } catch (\Exception $err) {
            // Just ignore
        }

        // Try to convert into a valid Duration
        try {
            return new Duration($value);
        } catch (\Exception $err) {
            // Just ignore
        }

        // Do not convert
        return $value;
    }
}
