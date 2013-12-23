<?php

/*
 * This file is part of the Xpl DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xpl\DateTime\Test\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Xpl\DateTime\DateTime;

/**
 * IntervalTest
 *
 * @author Oscar Cubo Medina <ocubo@cesvima.upm.es>
 */
class DateTimeTypeTest extends \Xpl\DateTime\Test\TestCase
{
    // Name of the type
    const TYPE_NAME  = 'TestingType';
    const TYPE_CLASS = '\\Xpl\\DateTime\\Doctrine\\Type\\DateTimeType';

    /**
     * Doctrine Platform Mock
     * @var MockPlatform
     */
    protected $platform;

    /**
     * Doctrine Type
     * @var Type
     */
    protected $type;

    /**
     * Setup test environment
     */
    public static function setUpBeforeClass()
    {
        // Call parent
        parent::setUpBeforeClass();

        // Register type
        if (Type::hasType(static::TYPE_NAME)) {
            Type::overrideType(static::TYPE_NAME, static::TYPE_CLASS);
        } else {
            Type::addType(static::TYPE_NAME, static::TYPE_CLASS);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // Obtain a mock for platform
        $this->platform = $this
            ->getMockBuilder('\\Doctrine\\DBAL\\Platforms\\AbstractPlatform')
            ->getMockForAbstractClass();

        // Recover registered type
        $this->type = Type::getType(self::TYPE_NAME);
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     *
     * Force default timezone to be UTC. Timezone management checked later
     */
    public function testDateTimeConvertsToDatabaseValue()
    {
        date_default_timezone_set('UTC');

        $date = new \DateTime('1985-09-01 10:10:10');

        $expected = $date->format($this->platform->getDateTimeTzFormatString());
        $actual = $this->type->convertToDatabaseValue($date, $this->platform);

        $this->assertEquals($expected, $actual);

        date_default_timezone_set(self::DEFAULT_TZ);
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     */
    public function testDateTimeConvertsToPHPValue()
    {
        // Birthday of jwage and also birthday of Doctrine. Send him a present ;)
        $date = $this->type->convertToPHPValue('1985-09-01 00:00:00', $this->platform);
        $this->assertInstanceOf('DateTime', $date);
        $this->assertEquals('1985-09-01 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     */
    public function testInvalidDateTimeFormatConversion()
    {
        $this->setExpectedException('Doctrine\DBAL\Types\ConversionException');
        $this->type->convertToPHPValue('abcdefg', $this->platform);
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     */
    public function testNullConversion()
    {
        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     */
    public function testConvertDateTimeToPHPValue()
    {
        $date = new \DateTime("now");
        $this->assertSame($date, $this->type->convertToPHPValue($date, $this->platform));
    }

    /**
     * From \Doctrine\Tests\DBAL\Types\DateTimeTest
     */
    public function testConvertsNonMatchingFormatToPhpValueWithParser()
    {
        $date = '1985/09/01 10:10:10.12345';

        $actual = $this->type->convertToPHPValue($date, $this->platform);

        $this->assertEquals('1985-09-01 10:10:10', $actual->format('Y-m-d H:i:s'));
    }

    /**
     * Must store any timezone but always recover values on UTC timezone
     */
    public function testTimezoneManagement()
    {
        // Create on a strange timestamp
        $timestamp = new DateTime('1985-09-01 10:10:10', self::PACIFIC_TZ);

        // Store and load
        $save = $this->type->convertToDatabaseValue($timestamp, $this->platform);
        $load = $this->type->convertToPHPValue($save, $this->platform);

        $this->assertTrue($timestamp == $load);
        $this->assertEquals('UTC', $load->getTimezone()->getName());
    }
}
