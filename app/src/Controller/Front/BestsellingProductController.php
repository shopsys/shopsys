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
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade
     */
    private $cachedBestsellingProductFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->cachedBestsellingProductFacade = $cachedBestsellingProductFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
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
