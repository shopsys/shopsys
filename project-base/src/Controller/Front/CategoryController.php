<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Category\CurrentCategoryResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Category\CurrentCategoryResolver
     */
    private $currentCategoryResolver;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade
     */
    private $topCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CurrentCategoryResolver $currentCategoryResolver
     * @param \Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade $topCategoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        Domain $domain,
        CategoryFacade $categoryFacade,
        CurrentCategoryResolver $currentCategoryResolver,
        TopCategoryFacade $topCategoryFacade,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
        $this->currentCategoryResolver = $currentCategoryResolver;
        $this->topCategoryFacade = $topCategoryFacade;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function panelAction(Request $request): Response
    {
        $categoriesWithLazyLoadedVisibleChildren = $this->categoryFacade->getCategoriesWithLazyLoadedVisibleChildrenForParent(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getCurrentDomainConfig()
        );
        $currentCategory = $this->currentCategoryResolver->findCurrentCategoryByRequest(
            $request,
            $this->domain->getId()
        );

        if ($currentCategory !== null) {
            $openCategories = $this->categoryFacade->getVisibleCategoriesInPathFromRootOnDomain(
                $currentCategory,
                $this->domain->getId()
            );
        } else {
            $openCategories = [];
        }

        return $this->render('Front/Content/Category/panel.html.twig', [
            'categoriesWithLazyLoadedVisibleChildren' => $categoriesWithLazyLoadedVisibleChildren,
            'isFirstLevel' => true,
            'openCategories' => $openCategories,
            'currentCategory' => $currentCategory,
        ]);
    }

    /**
     * @param int $parentCategoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function branchAction(int $parentCategoryId): Response
    {
        $parentCategory = $this->categoryFacade->getById($parentCategoryId);

        $categoriesWithLazyLoadedVisibleChildren = $this->categoryFacade->getCategoriesWithLazyLoadedVisibleChildrenForParent(
            $parentCategory,
            $this->domain->getCurrentDomainConfig()
        );

        return $this->render('Front/Content/Category/panel.html.twig', [
            'categoriesWithLazyLoadedVisibleChildren' => $categoriesWithLazyLoadedVisibleChildren,
            'isFirstLevel' => false,
            'openCategories' => [],
            'currentCategory' => null,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topAction(): Response
    {
        return $this->render('Front/Content/Category/top.html.twig', [
            'categories' => $this->topCategoryFacade->getVisibleCategoriesByDomainId($this->domain->getId()),
        ]);
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @param bool $showProductsCountByCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryListAction(array $categories, bool $showProductsCountByCategory = true): Response
    {
        if ($showProductsCountByCategory === true) {
            $pricingGroup = $this->currentCustomerUser->getPricingGroup();
            $domainId = $this->domain->getId();

            $listableProductCountsIndexedByCategoryId = $this->categoryFacade
                ->getListableProductCountsIndexedByCategoryId($categories, $pricingGroup, $domainId);
        } else {
            $listableProductCountsIndexedByCategoryId = [];
        }

        return $this->render('Front/Content/Category/categoryList.html.twig', [
            'categories' => $categories,
            'listableProductCountsIndexedByCategoryId' => $listableProductCountsIndexedByCategoryId,
        ]);
    }
}
