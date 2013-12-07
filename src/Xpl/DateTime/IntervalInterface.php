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

/**
 * Interval
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
interface IntervalInterface
{
    /**
     * Check if date is on the interface
     *
     * @param mixed $datetime Date&time specification
     *
     * @return boolean
     */
    public function contains($datetime);

    /**
     * From (start) date
     *
     * @return DateTime
     */
    public function getFromDate();

    /**
     * From (start) UNIX timestamp.
     *
     * May return -INF for unlimited intervals
     *
     * @return float
     */
    public function getFromTimestamp();

    /**
     * Till (finish) date
     *
     * May return +INF for unlimited intervals
     *
     * @return DateTime
     */
    public function getTillDate();

    /**
     * Till (finish) UNIX timestamp
     *
     * @return float
     */
    public function getTillTimestamp();

    /**
     * Duration of the period
     *
     * @return Duration
     */
    public function getDuration();

    /**
     * Converts the instance into a ISO8601 valid representation
     *
     * @return string
     */
    public function __toString();
}
