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
class TimestampTypeTest extends DateTimeTypeTest
{
    const TYPE_CLASS = '\\Xpl\\DateTime\\Doctrine\\Type\\TimestampType';

    /**
     * Must store and recover values on default timezone
     */
    public function testTimezoneManagement()
    {
        // Create on a strange timestamp
        $timestamp = new DateTime('2000-01-01 00:00:00', self::PACIFIC_TZ);

        // Store and load
        $save = $this->type->convertToDatabaseValue($timestamp, $this->platform);
        $load = $this->type->convertToPHPValue($save, $this->platform);

        $this->assertTrue($timestamp == $load);
        $this->assertEquals(self::DEFAULT_TZ, $load->getTimezone()->getName());
    }

    /**
     * Check that timestamp is used with Mysql Platform
     */
    public function testMysqlType()
    {
        $platform = $this
            ->getMockBuilder('\\Doctrine\\DBAL\\Platforms\\MySqlPlatform')
            ->getMockForAbstractClass();

        $this->assertEquals(
            'TIMESTAMP DEFAULT 0',
            $this->type->getSQLDeclaration(array('notnull' => true), $platform)
        );
        $this->assertEquals(
            'TIMESTAMP NULL',
            $this->type->getSQLDeclaration(array('notnull' => false), $platform)
        );
    }

    /**
     * Check that default declaration is used with other platforms
     */
    public function testNonMysqlType()
    {
        $platform = $this
            ->getMockBuilder('\\Doctrine\\DBAL\\Platforms\\SqlitePlatform')
            ->getMockForAbstractClass();
        $type = Type::getType('datetime');

        $this->assertEquals(
            $type->getSQLDeclaration(array('notnull' => true), $platform),
            $this->type->getSQLDeclaration(array('notnull' => true), $platform)
        );
        $this->assertEquals(
            $type->getSQLDeclaration(array('notnull' => false), $platform),
            $this->type->getSQLDeclaration(array('notnull' => false), $platform)
        );
    }
}
