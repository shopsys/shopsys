<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\NotificationBar;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarDataFactory;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade;
use Webmozart\Assert\Assert;

class NotificationBarCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly NotificationBarFacade $notificationBarFacade,
        protected readonly NotificationBarDataFactory $notificationBarDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): NotificationBar
    {
        return $this->notificationBarFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->notificationBarDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): NotificationBar
    {
        Assert::isInstanceOf($data, NotificationBarData::class);

        return $this->notificationBarFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, NotificationBar::class);

        return $this->notificationBarDataFactory->createFromNotificationBar($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, NotificationBar::class);
        Assert::isInstanceOf($data, NotificationBarData::class);

        $this->notificationBarFacade->edit($entity, $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, NotificationBar::class);

        $this->notificationBarFacade->delete($entity->getId());
    }
}
