<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;

class BestsellingProductController extends FrontBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        private readonly CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        private readonly Domain $domain,
        private readonly CurrentCustomerUser $currentCustomerUser
    ) {
    }

    /**
     * @param \App\Model\Category\Category $category
     */
    public function listAction(Category $category)
    {
        $bestsellingProducts = $this->cachedBestsellingProductFacade->getAllOfferedBestsellingProducts(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup()
        );

        return $this->render('Front/Content/Product/bestsellingProductsList.html.twig', [
            'products' => $bestsellingProducts,
            'maxShownProducts' => BestsellingProductFacade::MAX_SHOW_RESULTS,
        ]);
    }
}
