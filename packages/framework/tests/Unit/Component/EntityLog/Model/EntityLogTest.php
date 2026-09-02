<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\EntityLog\Model;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogData;

class EntityLogTest extends TestCase
{
    public function testChangeSetIsSerializedToPlainArrays(): void
    {
        $entityLogData = new EntityLogData();
        $entityLogData->action = 'update';
        $entityLogData->userIdentifier = 'admin';
        $entityLogData->entityName = 'Product';
        $entityLogData->entityId = 1;
        $entityLogData->entityIdentifier = 'product-1';
        $entityLogData->source = 'admin';
        $entityLogData->changeSet = [
            'name' => [
                'oldValue' => 'old',
                'newValue' => 'new',
            ],
            'createdAt' => [
                'oldValue' => null,
                'newValue' => new DateTimeImmutable('2024-01-01 12:00:00', new DateTimeZone('UTC')),
            ],
        ];

        $entityLog = new EntityLog($entityLogData);

        $this->assertSame([
            'name' => [
                'oldValue' => 'old',
                'newValue' => 'new',
            ],
            'createdAt' => [
                'oldValue' => null,
                'newValue' => [
                    'date' => '2024-01-01 12:00:00.000000',
                    'timezone_type' => 3,
                    'timezone' => 'UTC',
                ],
            ],
        ], $entityLog->getChangeSet());
    }
}
