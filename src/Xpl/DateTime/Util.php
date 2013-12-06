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
 * Try to convert specifications into valid PHP SPL DateTime objects.
 *
 * Used internally to ease the conversion in the constructors but it can be
 * useful in other cases.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class Util
{
    /**
     * Convert the value into its representation
     *
     * Disables error_reporting for this call (similar to as dirty '@' operator)
     *
     * @param mixed   $value The variable being converted.
     * @param boolean $force Force conversion (throw an exception on failure).
     *
     * @return string or null for empty values or false if conversion fails
     *
     * @see settype()
     */
    public static function toString($value, $force = false)
    {
        // All empty values converted to null
        if (empty($value)) {
            return null;
        }

        // Attempt to convert non-objects
        if (!is_object($value)) {
            // Attempt conversion
            $level = error_reporting(0); // No error reporting and backup level
            settype($value, 'string');   // Perform type conversion
            error_reporting($level);     // Restore error reporting level
        }

        // Nothing to convert (checks that non-object conversion was sucessful)
        if (is_string($value)) {
            return $value;
        }

        // Magic method available
        if (is_callable(array($value, '__toString'))) {
            return (string) $value;
        }

        // toString() method available
        if (is_callable(array($value, 'toString'))) {
            return $value->toString();
        }

        // Can not convert
        if ($force) {
            throw new InvalidArgumentException(
                sprintf('%s could not be converted to string', self::toTypeString($value))
            );
        }

        return false;
    }

    /**
     * Convert value to its type string
     *
     * @param mixed $value The variable being converted.
     *
     * @return string The type string
     */
    public static function toTypeString($value)
    {
        return is_object($value)
            ? sprintf('Object of class %s', get_class($value))
            : sprintf('Variable of type %s', gettype($value));
    }

    /**
     * Must not be instantiated.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
