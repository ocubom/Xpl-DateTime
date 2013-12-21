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
 * Namespaced DateTimeInterface (Marker interface pattern)
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
interface DateTimeInterface extends \DateTimeInterface
{
    /**
     * Magic method used when treated like a string
     *
     * Must return ISO 8601 compilant representation (RFC3339 recomended)
     *
     * @return string
     */
    public function __toString();
}
