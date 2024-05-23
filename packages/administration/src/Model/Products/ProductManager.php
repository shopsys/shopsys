<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Products;

use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class ProductManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory $productDataFactory
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactory $productDataFactory,
    ) {
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return Product::class;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    public function createDataObject(): ProductData
    {
        return $this->productDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function doCreate(AdminIdentifierInterface $dataObject): object
    {
        return $this->productFacade->create($dataObject, ProductRecalculationPriorityEnum::HIGH);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     */
    public function doDelete(object $object): void
    {
        $this->productFacade->delete($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $dataObject
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->productFacade->edit($dataObject->getId(), $dataObject, ProductRecalculationPriorityEnum::HIGH);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->productDataFactory->createFromProduct($entity);
    }
}
