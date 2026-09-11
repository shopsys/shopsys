<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\SalesRepresentative;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactory;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use Webmozart\Assert\Assert;

class SalesRepresentativeCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        protected readonly SalesRepresentativeDataFactory $salesRepresentativeDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->salesRepresentativeFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->salesRepresentativeDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, SalesRepresentativeData::class);

        return $this->salesRepresentativeFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, SalesRepresentative::class);

        return $this->salesRepresentativeDataFactory->createFromSalesRepresentative($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, SalesRepresentative::class);
        Assert::isInstanceOf($data, SalesRepresentativeData::class);

        $this->salesRepresentativeFacade->edit($entity, $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, SalesRepresentative::class);

        $this->salesRepresentativeFacade->delete($entity->getId());
    }
}
