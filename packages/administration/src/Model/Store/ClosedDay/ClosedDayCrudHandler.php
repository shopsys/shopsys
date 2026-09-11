<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Store\ClosedDay;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Webmozart\Assert\Assert;

class ClosedDayCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly ClosedDayDataFactory $closedDayDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->closedDayFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        $closedDayData = $this->closedDayDataFactory->create();
        $closedDayData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $closedDayData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, ClosedDayData::class);

        return $this->closedDayFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, ClosedDay::class);

        return $this->closedDayDataFactory->createFromClosedDay($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, ClosedDay::class);
        Assert::isInstanceOf($data, ClosedDayData::class);

        $this->closedDayFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, ClosedDay::class);

        $this->closedDayFacade->deleteById($entity->getId());
    }
}
