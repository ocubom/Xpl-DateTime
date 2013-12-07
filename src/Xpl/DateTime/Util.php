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
     * Try to create a valid object from a value
     *
     * @param string $value   Specification to conver
     * @param string $types   Type resolve order
     * @param string $default Value to return if conversion fails
     *
     * @return mixed object created or $default if could not convert
     */
    public static function create($value, $types, $default = false)
    {
        // Analize all resolvers
        foreach ($types as $type) {
            // Format resolve function
            $function = is_callable($type) ? $type : array(__CLASS__, 'create' . ucfirst($type));

            // Launch resolver
            $object = call_user_func($function, $value);

            // Resolver is sucessful
            if (false !== $object) {
                return $object;
            }
        }

        // All resolvers fails
        return $default;
    }

    /**
     * Try to convert specification into a valid DateInterval object
     *
     * @param string $value Interval specification
     *
     * @return \DateInterval or null for empty values or false if conversion fails
     */
    public static function createDateInterval($value)
    {
        // Convert empty values into null
        if (empty($value)) {
            return null;
        }

        // Do not convert DateInterval
        if ($value instanceof \DateInterval) {
            return $value;
        }

        // Numeric values are the total number of seconds of the interval
        if (is_numeric($value)) {
            return new \DateInterval(sprintf('PT%dS', $value));
        }

        // Try to convert into a string
        $value = self::toString($value);
        if (false === $value) {
            // Could no convert into string
            return false;
        }

        // Try to convert into a valid DateInterval
        try {
            // Extract sign
            $invert = '-' === $value[0] ? 1 : 0;
            $value  = trim($value, '+-');

            // Try with ISO format
            $interval = new \DateInterval($value);
            // Correct sign
            $interval->invert = $invert;

            return $interval;
        } catch (\Exception $err) {
            // Just ignore
        }

        // Try with date string format
        $interval = \DateInterval::createFromDateString($value);
        // Only returns with valid intervals
        if ('P0Y0M0DT0H0M0S' != $interval->format('%rP%yY%mM%dDT%hH%iM%sS')) {
            return $interval;
        }

        // No conversion found
        return false;
    }

    /**
     * Try to convert specification into a valid DateTime object
     *
     * @param string $value Value specification
     *
     * @return \DateTime or null for empty values or false if conversion fails
     */
    public static function createDateTime($value)
    {
        // Convert empty values into null
        if (empty($value)) {
            return null;
        }

        // Do not convert DateTime objects
        if ($value instanceof \DateTime) {
            return $value;
        }

        // Numeric values are UNIX timestamps
        if (is_numeric($value)) {
            return new \DateTime('@' . $value);
        }

        // Try to convert into a string
        $value = self::toString($value);
        if (false === $value) {
            // Could no convert into string
            return false;
        }

        try {
            return new \DateTime($value);
        } catch (\Exception $err) {
            // Just ignore
        }

        // No conversion found
        return false;
    }

    /**
     * Try to convert specification into a valid DateTime object
     *
     * @param string $value Value specification
     *
     * @return Interval or null for empty values or false if conversion fails
     */
    public static function createInterval($value)
    {
        // Convert empty values into null
        if (empty($value)) {
            return null;
        }

        // Do not convert Interval objects
        if ($value instanceof Interval) {
            return $value;
        }

        // Try to convert into a string
        $txt = Util::toString($value);
        if (false === $txt) {
            // Could no convert into string
            return false;
        }

        // '-' is a null value
        if ('-' === $txt) {
            return null;
        }

        // Parse ISO spec
        if (preg_match('@^(R(\d)+/([^/]+)/([^/]+)$@Uis', $txt, $matches)) {
            // Try to create interval with spec matches
            return new RepeatingInterval($txt);
        }

        // Parse ISO spec
        if (preg_match('@^([^/]+)/([^/]+)$@Uis', $txt, $matches)) {
            // Try to create interval with spec matches
            return new Interval($matches[1], $matches[2]);
        }

        // No conversion found
        return false;
    }

    /**
     * Check if object is a DateTime.
     *
     * Provides compatibility with PHP <5.5 (laks of \DateTimeInterface)
     *
     * @param mixed $object The object
     *
     * @return boolean
     */
    public static function isDateTime($object)
    {
        return $object instanceof \DateTimeInterface || $object instanceof \DateTime;
    }

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
        // Try direct string conversion
        $string = self::toString($value);
        if (false !== $string) {
            return $string;
        }

        // Return type of value
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
