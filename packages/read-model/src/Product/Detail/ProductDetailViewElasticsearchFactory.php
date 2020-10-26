<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFactory;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;
use Shopsys\ReadModelBundle\Product\PriceFactory;

class ProductDetailViewElasticsearchFactory
{
    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory
     */
    protected $productActionViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory
     */
    protected $parameterViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Brand\BrandViewFactory
     */
    protected $brandViewFactory;

    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterViewFactory $parameterViewFactory
     * @param \Shopsys\ReadModelBundle\Brand\BrandViewFactory $brandViewFactory
     */
    public function __construct(
        ImageViewFacadeInterface $imageViewFacade,
        CurrentCustomerUser $currentCustomerUser,
        ProductActionViewFactory $productActionViewFactory,
        ParameterViewFactory $parameterViewFactory,
        BrandViewFactory $brandViewFactory
    ) {
        $this->imageViewFacade = $imageViewFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productActionViewFactory = $productActionViewFactory;
        $this->parameterViewFactory = $parameterViewFactory;
        $this->brandViewFactory = $brandViewFactory;
    }

    /**
     * @param array $productArray
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function createFromProductArray(array $productArray): ProductDetailView
    {
        $parameterViews = [];
        foreach ($productArray['parameters'] as $parameterArray) {
            $parameterViews[] = $this->parameterViewFactory->createFromParameterArray($parameterArray);
        }

        return $this->createInstance(
            $productArray,
            $this->imageViewFacade->getAllImagesByEntityId(Product::class, $productArray['id']),
            $parameterViews,
            $this->brandViewFactory->createFromProductArray($productArray)
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Brand\BrandView $brandView
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    protected function createInstance(
        array $productArray,
        array $imageViews,
        array $parameterViews,
        BrandView $brandView
    ): ProductDetailView {
        return new ProductDetailView(
            $productArray['id'],
            $productArray['seo_h1'] ?: $productArray['name'],
            $productArray['description'],
            $productArray['availability'],
            PriceFactory::createProductPriceFromArrayByPricingGroup(
                $productArray['prices'],
                $this->currentCustomerUser->getPricingGroup()
            ),
            $productArray['catnum'],
            $productArray['partno'],
            $productArray['ean'],
            $productArray['main_category_id'],
            $productArray['calculated_selling_denied'],
            $productArray['in_stock'],
            $productArray['is_main_variant'],
            $productArray['main_variant_id'],
            $productArray['flags'],
            $productArray['seo_title'] ?: $productArray['name'],
            $productArray['seo_meta_description'],
            $this->productActionViewFactory->createFromArray($productArray),
            $brandView,
            $this->getMainImageView($imageViews),
            $imageViews,
            $parameterViews
        );
    }

    /**
     * @param array $imageViews
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
     */
    protected function getMainImageView(array $imageViews): ?ImageView
    {
        return Utils::getArrayValue($imageViews, 0, null);
    }
}
