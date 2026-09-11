<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\NotificationBar;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
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
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->notificationBarFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        $notificationBarData = $this->notificationBarDataFactory->create();
        $notificationBarData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $notificationBarData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, NotificationBarData::class);

        return $this->notificationBarFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, NotificationBar::class);

        return $this->notificationBarDataFactory->createFromNotificationBar($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, NotificationBar::class);
        Assert::isInstanceOf($data, NotificationBarData::class);

        $this->notificationBarFacade->edit($entity, $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, NotificationBar::class);

        $this->notificationBarFacade->delete($entity->getId());
    }
}
