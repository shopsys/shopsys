<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\EntityLog\Timeline\TwigComponent;

use DateTimeImmutable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\EntityLog\Timeline\TwigComponent\EntityLogTimelineComponent;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogSourceEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;

class EntityLogTimelineComponentTest extends TestCase
{
    private const string FORMATTED_TEXT = 'formatted:';

    public function testGroupsEntityLogsByLogCollectionNumberAndKeepsOriginalInstances(): void
    {
        $orderLog = $this->createEntityLog(
            1,
            'entityLog1.1',
            EntityLogActionEnum::UPDATE,
            'Order',
            10,
            '123456',
            [
                'city' => [
                    'newReadableValue' => 'Prague',
                ],
            ],
        );
        $orderItemLog = $this->createEntityLog(
            2,
            'entityLog1.1',
            EntityLogActionEnum::CREATE,
            'OrderItem',
            20,
            'Coffee',
            [
                'name' => [
                    'newReadableValue' => 'Coffee',
                ],
            ],
            'Order',
            10,
        );
        $olderLog = $this->createEntityLog(
            3,
            'entityLog1.0',
            EntityLogActionEnum::CREATE,
            'Order',
            10,
            '123456',
            [],
        );
        $component = $this->createComponent([$orderLog, $orderItemLog, $olderLog]);

        $groups = $component->getGroups();

        $this->assertCount(2, $groups);
        $this->assertCount(2, $groups[0]);
        $this->assertSame($orderLog, $groups[0][0]);
        $this->assertSame($orderItemLog, $groups[0][1]);
        $this->assertCount(1, $groups[1]);
        $this->assertSame($olderLog, $groups[1][0]);
        $this->assertSame('mixed', $component->getGroupAction($groups[0]));
        $this->assertSame(self::FORMATTED_TEXT . 'Prague', $component->getFormattedChanges($groups[0][0]));
        $this->assertSame(self::FORMATTED_TEXT . 'Coffee', $component->getFormattedChanges($groups[0][1]));
    }

    public function testGroupUsesSpecificActionAndLatestCreatedAtWhenValuesAreShared(): void
    {
        $olderLog = $this->createEntityLog(
            1,
            'entityLog1',
            EntityLogActionEnum::CREATE,
            'Order',
            10,
            '123456',
            [],
            null,
            null,
            new DateTimeImmutable('2026-05-28 10:00:00'),
        );
        $newerLog = $this->createEntityLog(
            2,
            'entityLog1',
            EntityLogActionEnum::CREATE,
            'OrderItem',
            20,
            'Coffee',
            [],
            'Order',
            10,
            new DateTimeImmutable('2026-05-28 10:05:00'),
        );
        $component = $this->createComponent([$olderLog, $newerLog]);

        $groups = $component->getGroups();

        $this->assertCount(1, $groups);
        $this->assertSame(EntityLogActionEnum::CREATE, $component->getGroupAction($groups[0]));
        $this->assertEquals(new DateTimeImmutable('2026-05-28 10:05:00'), $component->getGroupCreatedAt($groups[0]));
    }

    public function testEmptyLogCollectionNumberCreatesSeparateFallbackGroups(): void
    {
        $firstLog = $this->createEntityLog(
            1,
            '',
            EntityLogActionEnum::UPDATE,
            'Order',
            10,
            '123456',
            [],
        );
        $secondLog = $this->createEntityLog(
            2,
            '',
            EntityLogActionEnum::UPDATE,
            'Order',
            10,
            '123456',
            [],
        );
        $component = $this->createComponent([$firstLog, $secondLog]);

        $groups = $component->getGroups();

        $this->assertCount(2, $groups);
        $this->assertSame($firstLog, $groups[0][0]);
        $this->assertSame($secondLog, $groups[1][0]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[] $entityLogs
     */
    protected function createComponent(array $entityLogs): EntityLogTimelineComponent
    {
        $component = new EntityLogTimelineComponent(
            $this->createEntityLogRepositoryStub($entityLogs),
            $this->createResolvedChangesFormatterStub(),
        );
        $component->entityName = 'Order';
        $component->entityId = 10;

        return $component;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[] $entityLogs
     */
    protected function createEntityLogRepositoryStub(array $entityLogs): EntityLogRepository
    {
        $query = $this->createStub(Query::class);
        $query->method('getResult')->willReturn($entityLogs);

        $queryBuilder = $this->createStub(QueryBuilder::class);
        $queryBuilder->method('getQuery')->willReturn($query);

        $entityLogRepository = $this->createStub(EntityLogRepository::class);
        $entityLogRepository->method('getQueryBuilderByEntityNameAndEntityId')->willReturn($queryBuilder);

        return $entityLogRepository;
    }

    protected function createResolvedChangesFormatterStub(): ResolvedChangesFormatter
    {
        $resolvedChangesFormatterStub = $this->createStub(ResolvedChangesFormatter::class);
        $resolvedChangesFormatterStub
            ->method('formatResolvedChanges')
            ->willReturnCallback(static function (array $changeSet): string {
                if ($changeSet === []) {
                    return '';
                }

                $firstChange = reset($changeSet);

                return self::FORMATTED_TEXT . $firstChange['newReadableValue'];
            });

        return $resolvedChangesFormatterStub;
    }

    /**
     * @param array<string, array<string, string>> $changeSet
     */
    protected function createEntityLog(
        int $id,
        string $logCollectionNumber,
        string $action,
        string $entityName,
        int $entityId,
        string $entityIdentifier,
        array $changeSet,
        ?string $parentEntityName = null,
        ?int $parentEntityId = null,
        ?DateTimeImmutable $createdAt = null,
    ): EntityLog {
        $entityLog = $this->createStub(EntityLog::class);
        $entityLog->method('getId')->willReturn($id);
        $entityLog->method('getLogCollectionNumber')->willReturn($logCollectionNumber);
        $entityLog->method('getAction')->willReturn($action);
        $entityLog->method('getEntityName')->willReturn($entityName);
        $entityLog->method('getEntityId')->willReturn($entityId);
        $entityLog->method('getEntityIdentifier')->willReturn($entityIdentifier);
        $entityLog->method('getSource')->willReturn(EntityLogSourceEnum::ADMIN);
        $entityLog->method('getUserIdentifier')->willReturn('admin@example.com');
        $entityLog->method('getCreatedAt')->willReturn($createdAt ?? new DateTimeImmutable('2026-05-28 10:00:00'));
        $entityLog->method('getChangeSet')->willReturn($changeSet);
        $entityLog->method('getParentEntityName')->willReturn($parentEntityName);
        $entityLog->method('getParentEntityId')->willReturn($parentEntityId);

        return $entityLog;
    }
}
