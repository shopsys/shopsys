<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Navigation;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDataFactory;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFacade;
use Webmozart\Assert\Assert;

class NavigationItemCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly NavigationItemFacade $navigationItemFacade,
        protected readonly NavigationItemDataFactory $navigationItemDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): NavigationItem
    {
        return $this->navigationItemFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        return $this->navigationItemDataFactory->createNew();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): NavigationItem
    {
        Assert::isInstanceOf($data, NavigationItemData::class);

        return $this->navigationItemFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(Presentable $entity): object
    {
        Assert::isInstanceOf($entity, NavigationItem::class);

        return $this->navigationItemDataFactory->createForEntity($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(Presentable $entity, object $data): void
    {
        Assert::isInstanceOf($entity, NavigationItem::class);
        Assert::isInstanceOf($data, NavigationItemData::class);

        $this->navigationItemFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(Presentable $entity): void
    {
        Assert::isInstanceOf($entity, NavigationItem::class);

        $this->navigationItemFacade->delete($entity);
    }
}
