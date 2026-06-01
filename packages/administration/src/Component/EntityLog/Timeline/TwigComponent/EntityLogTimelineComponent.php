<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\EntityLog\Timeline\TwigComponent;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Admin:EntityLogTimeline',
    template: '@ShopsysAdministration/content/entityLog/timeline.html.twig',
)]
class EntityLogTimelineComponent
{
    protected const string MIXED_VALUE = 'mixed';

    public string $entityName;

    public int $entityId;

    public function __construct(
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly ResolvedChangesFormatter $resolvedChangesFormatter,
    ) {
    }

    /**
     * @return array<int, array<int, \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog>>
     */
    public function getGroups(): array
    {
        $entityLogs = $this->entityLogRepository
            ->getQueryBuilderByEntityNameAndEntityId($this->entityName, $this->entityId)
            ->getQuery()
            ->getResult();

        $itemsByCollectionNumber = [];

        foreach ($entityLogs as $entityLog) {
            $collectionNumber = $entityLog->getLogCollectionNumber();

            if ($collectionNumber === '') {
                $collectionNumber = 'entity-log-' . $entityLog->getId();
            }

            $itemsByCollectionNumber[$collectionNumber][] = $entityLog;
        }

        return array_values($itemsByCollectionNumber);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[] $entityLogs
     */
    public function getGroupAction(array $entityLogs): string
    {
        return $this->resolveGroupValue(
            $entityLogs,
            static fn (EntityLog $entityLog): string => $entityLog->getAction(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[] $entityLogs
     */
    public function getGroupCreatedAt(array $entityLogs): DateTimeImmutable
    {
        $createdAt = $entityLogs[0]->getCreatedAt();

        foreach ($entityLogs as $entityLog) {
            if ($entityLog->getCreatedAt() > $createdAt) {
                $createdAt = $entityLog->getCreatedAt();
            }
        }

        return $createdAt;
    }

    public function getFormattedChanges(EntityLog $entityLog): string
    {
        return $this->resolvedChangesFormatter->formatResolvedChanges($entityLog->getChangeSet());
    }

    public function getActionIconName(string $action): string
    {
        return match ($action) {
            EntityLogActionEnum::CREATE => 'plus',
            EntityLogActionEnum::UPDATE => 'pencil',
            EntityLogActionEnum::DELETE => 'trash',
            default => 'list-search',
        };
    }

    public function getActionIconClass(string $action): string
    {
        return match ($action) {
            EntityLogActionEnum::CREATE => 'text-green',
            EntityLogActionEnum::UPDATE => 'text-blue',
            EntityLogActionEnum::DELETE => 'text-red',
            default => 'text-secondary',
        };
    }

    public function getActionBadgeClass(string $action): string
    {
        return match ($action) {
            EntityLogActionEnum::CREATE => 'bg-green-lt text-green-lt-fg',
            EntityLogActionEnum::UPDATE => 'bg-blue-lt text-blue-lt-fg',
            EntityLogActionEnum::DELETE => 'bg-red-lt text-red-lt-fg',
            default => 'bg-secondary-lt',
        };
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[] $entityLogs
     * @param callable(\Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog): string $valueGetter
     */
    protected function resolveGroupValue(array $entityLogs, callable $valueGetter): string
    {
        $values = array_unique(array_map($valueGetter, $entityLogs));

        if (count($values) === 1) {
            return array_values($values)[0];
        }

        return static::MIXED_VALUE;
    }
}
