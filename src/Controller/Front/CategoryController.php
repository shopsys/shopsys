<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\Category\CurrentCategoryResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends FrontBaseController
{
    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Category\CurrentCategoryResolver
     */
    private $currentCategoryResolver;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CurrentCategoryResolver $currentCategoryResolver
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        Domain $domain,
        CategoryFacade $categoryFacade,
        CurrentCategoryResolver $currentCategoryResolver,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
        $this->currentCategoryResolver = $currentCategoryResolver;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mobileSlidingMenuAction(): Response
    {
        $topCategories = $this->categoryFacade->getTranslatedVisibleSubcategoriesByDomain(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getCurrentDomainConfig()
        );

        $menuItems = [];
        foreach ($topCategories as $category) {
            if ($category->getAkeneoCode() === 'eshop__nabytek') {
                $menuItems[0] = $this->buildSlidingMenuSetup('front_product_list', $category->getId(), $category->getName($this->domain->getCurrentDomainConfig()->getLocale()));

                $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent = $this->getMattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent($category);
                $menuItems[2] = $this->buildSlidingMenuSetup(
                    'front_product_list',
                    $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent->getCategory()->getId(),
                    $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent->getCategory()->getName($this->domain->getCurrentDomainConfig()->getLocale())
                );
            }
        }
        $menuItems[1] = $this->buildSlidingMenuSetup('front_kitchen', null, t('Kuchyně'));
        $menuItems[3] = $this->buildSlidingMenuSetup('front_productseries_list', null, t('Nábytkové programy'));

        $saleCategory = $this->categoryFacade->findSaleCategory();
        if ($saleCategory !== null) {
            $menuItems[4] = $this->buildSlidingMenuSetup(
                'front_product_list',
                $saleCategory->getId(),
                $saleCategory->getName($this->domain->getCurrentDomainConfig()->getLocale())
            );
        }

        ksort($menuItems);


        return $this->render('Front/Content/Category/mobileSlidingMenu.html.twig', [
            'menuItems' => $menuItems
        ]);
    }

    /**
     * @param string $route
     * @param int|null $id
     * @param string $name
     * @return array
     */
    private function buildSlidingMenuSetup(string $route, ?int $id, string $name): array
    {
        return [
            'route' => $route,
            'id' => $id,
            'name' => $name,
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mobilePanelMenuAction(): Response
    {
        $categoriesWithLazyLoadedVisibleChildren = $this->categoryFacade->getCategoriesWithLazyLoadedVisibleChildrenForParent(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getCurrentDomainConfig()
        );

        $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent = null;
        $filteredCategoriesWithLazyLoadedVisibleChildren = [];
        foreach ($categoriesWithLazyLoadedVisibleChildren as $categoryWithLazyLoadedVisibleChildren) {
            /** @var \App\Model\Category\Category $category */
            $category = $categoryWithLazyLoadedVisibleChildren->getCategory();

            if ($category->getAkeneoCode() === 'eshop__nabytek') {
                $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent = $this->getMattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent($category);
                $filteredCategoriesWithLazyLoadedVisibleChildren[] = $categoryWithLazyLoadedVisibleChildren;
            }
        }

        return $this->render('Front/Content/Category/mobilePanelMenu.html.twig', [
            'categoriesWithLazyLoadedVisibleChildren' => $filteredCategoriesWithLazyLoadedVisibleChildren,
            'isFirstLevel' => true,
            'saleCategory' => $this->categoryFacade->findSaleCategory(),
            'mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent' => $mattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent
        ]);
    }

    /**
     * @param \App\Model\Category\Category $furnitureCategory
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren|null
     */
    private function getMattressesAndSlatsCategoryWithLazyLoadedVisibleChildrenForParent(Category $furnitureCategory): ?CategoryWithLazyLoadedVisibleChildren
    {
        $categoriesWithLazyLoadedVisibleChildren = $this->categoryFacade->getCategoriesWithLazyLoadedVisibleChildrenForParent(
            $furnitureCategory,
            $this->domain->getCurrentDomainConfig()
        );

        foreach ($categoriesWithLazyLoadedVisibleChildren as $categoryWithLazyLoadedVisibleChildren) {
            /** @var \App\Model\Category\Category $category */
            $category = $categoryWithLazyLoadedVisibleChildren->getCategory();

            if ($category->getAkeneoCode() === 'eshop__matrace_a_rosty') {
                return $categoryWithLazyLoadedVisibleChildren;
            }
        }

        return null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function panelAction(Request $request)
    {
        $categoriesWithLazyLoadedVisibleChildren = $this->categoryFacade->getCategoriesWithLazyLoadedVisibleChildrenForParent(
            $this->categoryFacade->getRootCategory(),
            $this->domain->getCurrentDomainConfig()
        );
        $currentCategory = $this->currentCategoryResolver->findCurrentCategoryByRequest($request, $this->domain->getId());

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
            'saleCategory' => $this->categoryFacade->findSaleCategory(),
        ]);
    }

    /**
     * @param int $parentCategoryId
     */
    public function branchAction($parentCategoryId)
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
            'saleCategory' => $this->categoryFacade->findSaleCategory(),
        ]);
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @param bool $showProductsCountByCategory
     * @param string $cssClass
     */
    public function categoryListAction(array $categories, $showProductsCountByCategory = true, $cssClass = null)
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
            'cssClass' => $cssClass,
            'listableProductCountsIndexedByCategoryId' => $listableProductCountsIndexedByCategoryId,
        ]);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param bool $showProductsCountByCategory
     * @param string|null $cssClass
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryChildrenListAction(Category $category, bool $showProductsCountByCategory = true, ?string $cssClass = null): Response
    {
        $categories = $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId($category, $this->domain->getId());

        return $this->render('Front/Content/Category/categoryChildrenList.html.twig', [
            'categories' => $categories,
            'showProductsCountByCategory' => $showProductsCountByCategory,
            'cssClass' => $cssClass,
        ]);
    }
}
