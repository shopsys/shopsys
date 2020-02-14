<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use App\Form\Admin\Product\Type\ProductTypeFormType;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Symfony\Component\Form\FormFactoryInterface;

class ProductTypeInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    protected $productTypeFacade;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @param \App\Model\Product\Type\ProductTypeGridFactory $productTypeGridFactory
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    public function __construct(
        ProductTypeGridFactory $productTypeGridFactory,
        ProductTypeFacade $productTypeFacade,
        FormFactoryInterface $formFactory
    ) {
        parent::__construct($productTypeGridFactory);
        $this->productTypeFacade = $productTypeFacade;
        $this->formFactory = $formFactory;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     * @return int
     */
    protected function createEntityAndGetId($productTypeData)
    {
        $productType = $this->productTypeFacade->create($productTypeData);

        return $productType->getId();
    }

    /**
     * @param int $productTypeId
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    protected function editEntity($productTypeId, $productTypeData)
    {
        $this->productTypeFacade->edit($productTypeId, $productTypeData);
    }

    /**
     * @param int|null $productTypeId
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm($productTypeId)
    {
        $productTypeData = new ProductTypeData();

        if ($productTypeId !== null) {
            $productType = $this->productTypeFacade->getById((int)$productTypeId);
            $productTypeData->fillFromProductType($productType);
        }

        return $this->formFactory->create(ProductTypeFormType::class, $productTypeData);
    }
}
