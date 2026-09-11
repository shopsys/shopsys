<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Store;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Store\Exception\DefaultStoreCannotBeDeletedException;
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
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->storeFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->storeDataFactory->createForDomain($this->adminDomainTabsFacade->getSelectedDomainId());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, StoreData::class);

        return $this->storeFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, Store::class);

        return $this->storeDataFactory->createFromStore($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, Store::class);
        Assert::isInstanceOf($data, StoreData::class);

        $this->storeFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     *
     * The default store is protected in the grid (its delete action is disabled), this guard covers direct requests
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, Store::class);

        if ($entity->isDefault()) {
            throw new DefaultStoreCannotBeDeletedException($entity);
        }

        $this->storeFacade->delete($entity->getId());
    }
}
