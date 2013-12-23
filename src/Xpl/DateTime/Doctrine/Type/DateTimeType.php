<?php

/*
 * This file is part of the Xpl DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType as DateTimeTypeBase;
use Xpl\DateTime\DateTime;
use Xpl\DateTime\Util;

/**
 * Safe DateTime type
 *
 * This type deals with timezones by converting the data to UTC (idea taken
 * from http://bit.ly/10LnngH). All data on database is stored on UTC so all
 * other clients must use it.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class DateTimeType extends DateTimeTypeBase
{
    /**
     * Cached UTC timezone
     *
     * @var \DateTimeZone
     */
    static private $timezone = null;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return parent::getName() . 'utc';
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        // Convert to use redefined format
        $datetime = $value instanceof DateTime ? $value : new DateTime($value);

        // Write value on UTC timezone
        return $datetime->format(
            $platform->getDateTimeFormatString(),
            (self::$timezone) ?: (self::$timezone = new \DateTimeZone('UTC'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Pass null values or
        if (null === $value || Util::isDateTime($value)) {
            return $value;
        }

        try {
            // Parse value using format on UTC timezone
            return DateTime::createFromFormat(
                $platform->getDateTimeFormatString(),
                $value,
                (self::$timezone) ?: (self::$timezone = new \DateTimeZone('UTC'))
            );
        } catch (\Exception $err) {
            // Just ignore
        }

        try {
            // Try to create a new datetime on UTC timezone
            return new DateTime(
                $value,
                (self::$timezone) ?: (self::$timezone = new \DateTimeZone('UTC'))
            );
        } catch (\Exception $err) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }
}
