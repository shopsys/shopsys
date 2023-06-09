<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\Exception\MethodIsNotAllowedException;

class AbstractMigrationTest extends TestCase
{
    public function testAddSqlException()
    {
        $abstractMigrationMock = $this->getMockBuilder(AbstractMigration::class)
            ->setMethods(['addSql'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $reflectionClass = new ReflectionClass(AbstractMigration::class);
        $addSqlMethod = $reflectionClass->getMethod('addSql');
        $addSqlMethod->setAccessible(true);

        $this->expectException(MethodIsNotAllowedException::class);

        $addSqlMethod->invokeArgs($abstractMigrationMock, ['']);
    }
}
