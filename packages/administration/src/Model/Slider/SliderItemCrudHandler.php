<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\Slider;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Webmozart\Assert\Assert;

class SliderItemCrudHandler implements CrudHandlerInterface
{
    public function __construct(
        protected readonly SliderItemFacade $sliderItemFacade,
        protected readonly SliderItemDataFactory $sliderItemDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->sliderItemFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createData(): object
    {
        $sliderItemData = $this->sliderItemDataFactory->create();
        $sliderItemData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $sliderItemData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, SliderItemData::class);

        return $this->sliderItemFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, SliderItem::class);

        return $this->sliderItemDataFactory->createFromSliderItem($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, SliderItem::class);
        Assert::isInstanceOf($data, SliderItemData::class);

        $this->sliderItemFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, SliderItem::class);

        $this->sliderItemFacade->delete($entity->getId());
    }
}
