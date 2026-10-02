<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Store;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreDataFactory;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Webmozart\Assert\Assert;

class StoreCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly StoreDataFactory $storeDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Store
    {
        return $this->storeFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->storeDataFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Store
    {
        Assert::isInstanceOf($data, StoreData::class);

        return $this->storeFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, Store::class);

        return $this->storeDataFactory->createFromStore($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Store::class);
        Assert::isInstanceOf($data, StoreData::class);

        $this->storeFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, Store::class);

        $this->storeFacade->delete($entity->getId());
    }
}
