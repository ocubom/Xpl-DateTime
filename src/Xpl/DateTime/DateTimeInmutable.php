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
 * Inmutable representation of date and time.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DateTimeInmutable extends DateTime
{
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

        // Check methods that change state
        if ('set' == substr($method, 0, 3) || in_array($method, array('add', 'format', 'modify', 'sub'))) {
            // Call parent on cloned instance
            return call_user_func_array(array(new DateTime($this), 'callParent'), func_get_args());
        }

        // Call parent
        return call_user_func_array(array('parent', 'callParent'), func_get_args());
    }
}
