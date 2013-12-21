<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {

    // Check if PHP defines interface
    if (interface_exists('DateTimeInterface', false)) {
        return; // Already defined, just abort
    }

    /**
     * DateTimeInterface
     *
     * Provides compatibility with PHP 5.5+ (registered on global namespace)
     *
     * @author Oscar Cubo Medina <ocubom@gmail.com>
     */
    interface DateTimeInterface
    {
        /**
         * Difference between two DateTime objects
         *
         * @param mixed $datetime Date&time specification
         * @param bool  $absolute Should the interval be forced to be positive?
         *
         * @return \DateInterval
         *
         * @see \DateTimeIn::diff()
         */
        public function diff($datetime, $absolute = false);

        /**
         * Date formatted according to given format.
         *
         * @param string $format Format accepted by date().
         *
         * @return string Formatted date
         *
         * @see \DateTimeInterface::format()
         */
        public function format($format);

        /**
         * Timezone offset in seconds
         *
         * @return int
         *
         * @see \DateTimeInterface::getOffset()
         */
        public function getOffset();

        /**
         * Unix timestamp
         *
         * @return integer The Unix timestamp representing the date
         *
         * @see \DateTimeInterface::getTimestamp()
         */
        public function getTimestamp();

        /**
         * Timezone
         *
         * @return \DateTimeZone The Timezone
         *
         * @see \DateTimeInterface::getTimezone()
         */
        public function getTimezone();

        /**
         * The __wakeup() handler
         *
         * @see \DateTimeInterface::__wakeup()
         */
        public function __wakeup();
    }
}
