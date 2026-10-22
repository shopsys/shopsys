<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\EntityLog\Timeline\TwigComponent;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Webmozart\Assert\Assert;

#[AsLiveComponent(
    name: 'Admin:EntityLogTimeline',
    template: '@ShopsysAdministration/content/entityLog/timeline.html.twig',
)]
class EntityLogTimelineComponent
{
    use DefaultActionTrait;

    protected const string MIXED_VALUE = 'mixed';

    protected const int ENTITY_LOGS_PER_BATCH = 20;

    #[LiveProp]
    public string $entityName;

    #[LiveProp]
    public int $entityId;

    #[LiveProp]
    public ?int $currentOldestEntityLogId = null;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[]|null
     */
    protected ?array $entityLogsBatch = null;

    public function __construct(
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly ResolvedChangesFormatter $resolvedChangesFormatter,
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
    }

    /**
     * The timeline is identified either by the logged entity itself or by the "entityName" and "entityId" props
     */
    public function mount(?object $entity = null): void
    {
        if ($entity === null) {
            return;
        }

        Assert::methodExists($entity, 'getId', 'Only entities with getId() have an entity log, "%s" has none.');

        $this->entityName = $this->entityLogFacade->getEntityNameByEntity($entity);
        $this->entityId = $entity->getId();
    }

    #[LiveAction]
    #[RequireRole(SystemRole::ADMIN)]
    public function loadMore(): void
    {
        $oldestEntityLogId = $this->getOldestEntityLogId();

        if ($oldestEntityLogId === null) {
            return;
        }

        $this->currentOldestEntityLogId = $oldestEntityLogId;
        $this->entityLogsBatch = null;
    }

    /**
     * @return array<int, array<int, \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog>>
     */
    public function getGroups(): array
    {
        $itemsByCollectionNumber = [];

        foreach ($this->getEntityLogsBatch() as $entityLog) {
            $collectionNumber = $entityLog->getLogCollectionNumber();

            if ($collectionNumber === '') {
                $collectionNumber = 'entity-log-' . $entityLog->getId();
            }

            $itemsByCollectionNumber[$collectionNumber][] = $entityLog;
        }

        return array_values($itemsByCollectionNumber);
    }

    public function hasMoreEntityLogs(): bool
    {
        $oldestEntityLogId = $this->getOldestEntityLogId();

        return $oldestEntityLogId !== null
            && $this->entityLogRepository->hasEntityLogsOlderThan($this->entityName, $this->entityId, $oldestEntityLogId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[]
     */
    protected function getEntityLogsBatch(): array
    {
        if ($this->entityLogsBatch === null) {
            $this->entityLogsBatch = $this->entityLogRepository->getEntityLogsBatch(
                $this->entityName,
                $this->entityId,
                $this->currentOldestEntityLogId,
                static::ENTITY_LOGS_PER_BATCH,
            );
        }

        return $this->entityLogsBatch;
    }

    protected function getOldestEntityLogId(): ?int
    {
        $entityLogsBatch = $this->getEntityLogsBatch();
        $oldestEntityLog = end($entityLogsBatch);

        return $oldestEntityLog === false ? null : $oldestEntityLog->getId();
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
