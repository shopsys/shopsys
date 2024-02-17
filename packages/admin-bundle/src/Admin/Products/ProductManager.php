<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin\Products;

use App\Model\Product\Product;
use Shopsys\AdminBundle\Component\Admin\AbstractDtoManager;
use Shopsys\AdminBundle\Component\AdminIdentifierInterface;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use App\Model\Product\ProductFactory;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Symfony\Contracts\Service\Attribute\Required;

class ProductManager extends AbstractDtoManager
{
    #[Required]
    public ProductFacade $productFacade;

    #[Required]
    public ProductDataFactory $productDataFactory;

    #[Required]
    public ProductFactory $productFactory;

    public function getSubjectClass()
    {
        return Product::class;
    }

    public function createDataObject()
    {
        return $this->productDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $dataObject
     * @return object
     */
    public function doCreate(AdminIdentifierInterface $dataObject): object
    {
        return $this->productFacade->create($dataObject, ProductRecalculationPriorityEnum::HIGH);
    }

    public function doDelete(object $object)
    {
        $this->productFacade->delete($object->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $dataObject
     * @return object
     */
    public function doEdit(AdminIdentifierInterface $dataObject): object
    {
        return $this->productFacade->edit($dataObject->getId(), $dataObject, ProductRecalculationPriorityEnum::HIGH);
    }

    public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface
    {
        return $this->productDataFactory->createFromProduct($entity);
    }
}