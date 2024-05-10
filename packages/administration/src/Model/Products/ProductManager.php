<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Products;

use Doctrine\Persistence\ManagerRegistry;
use Shopsys\Administration\Component\Admin\AbstractDtoManager;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ProductManager extends AbstractDtoManager
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductDataFactory $productDataFactory,
        EntityNameResolver $entityNameResolver,
        ManagerRegistry $registry,
        PropertyAccessorInterface $propertyAccessor,
    ) {
        parent::__construct($entityNameResolver, $registry, $propertyAccessor);
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
