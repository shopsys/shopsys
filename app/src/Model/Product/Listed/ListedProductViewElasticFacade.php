<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use App\Model\Product\Action\ProductActionViewFactory;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewElasticFacade as BaseListedProductViewElasticFacade;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
 */
class ListedProductViewElasticFacade extends BaseListedProductViewElasticFacade
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    protected $productAvailabilityFacade;

    /**
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \App\Model\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductAccessoryFacade $productAccessoryFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        TopProductFacade $topProductFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ListedProductViewFactory $listedProductViewFactory,
        ProductActionViewFacade $productActionViewFacade,
        ImageViewFacade $imageViewFacade,
        ProductAvailabilityFacade $productAvailabilityFacade,
        ProductActionViewFactory $productActionViewFactory,
        ProductElasticsearchProvider $productElasticsearchProvider
    ) {
        parent::__construct(
            $productFacade,
            $productAccessoryFacade,
            $domain,
            $currentCustomerUser,
            $topProductFacade,
            $productOnCurrentDomainFacade,
            $listedProductViewFactory,
            $productActionViewFacade,
            $imageViewFacade,
            $productActionViewFactory,
            $productElasticsearchProvider
        );

        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }
}
