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
use Xpl\DateTime\TimeZone;
use Xpl\DateTime\Util;

/**
 * Use timestamp type for storing date/times.
 *
 * This solves the timezone problem if the connection is configured in the same
 * timezone as PHP. The data stored on database will be timezone agnostic.
 *
 * This only replaces MySQL definition. All other platforms remains untouched.
 *
 * @author Oscar Cubo Medina <ocubom@gmail.com>
 */
class TimestampType extends DateTimeTypeBase
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'timestamp';
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // Change declaration only for MySQL
        if ($platform instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
            return 'TIMESTAMP ' . ($fieldDeclaration['notnull'] ? 'DEFAULT 0' : 'NULL');
        }

        // Return parent declaration for other platforms
        return parent::getSQLDeclaration($fieldDeclaration, $platform);
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

        // Write value on default timezone
        return $datetime->format(
            $platform->getDateTimeFormatString(),
            date_default_timezone_get()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        try {
            // Parse value using format on default timezone
            return DateTime::createFromFormat(
                $platform->getDateTimeFormatString(),
                $value,
                date_default_timezone_get()
            );
        } catch (\Exception $err) {
            // Just ignore
        }

        try {
            // Try to create a new datetime on default timezone
            return new DateTime(
                $value,
                date_default_timezone_get()
            );
        } catch (\Exception $err) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }
}
