<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Category;

use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class CategoryRepositoryTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     * @inject
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     * @inject
     */
    private $categoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository
     * @inject
     */
    private $categoryVisibilityRepository;

    /**
     * @var \Shopsys\ShopBundle\Model\Category\CategoryDataFactory
     * @inject
     */
    private $categoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     * @inject
     */
    private $localization;

    public function testDoNotGetCategoriesWithoutVisibleChildren()
    {
        $categoryData = $this->categoryDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'name';
        }
        $categoryData->name = $names;
        /** @var \Shopsys\ShopBundle\Model\Category\Category $rootCategory */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData->parent = $rootCategory;

        /** @var \Shopsys\ShopBundle\Model\Category\Category $parentCategory */
        $parentCategory = $this->categoryFacade->create($categoryData);

        $categoryData->enabled[self::FIRST_DOMAIN_ID] = false;
        $categoryData->enabled[self::SECOND_DOMAIN_ID] = false;

        $categoryData->parent = $parentCategory;
        $this->categoryFacade->create($categoryData);

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $categoriesWithVisibleChildren = $this->categoryRepository->getCategoriesWithVisibleChildren([$parentCategory], self::FIRST_DOMAIN_ID);
        $this->assertCount(0, $categoriesWithVisibleChildren);
    }

    public function testGetCategoriesWithAtLeastOneVisibleChild()
    {
        $categoryData = $this->categoryDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'name';
        }
        $categoryData->name = $names;
        /** @var \Shopsys\ShopBundle\Model\Category\Category $rootCategory */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData->parent = $rootCategory;

        /** @var \Shopsys\ShopBundle\Model\Category\Category $parentCategory */
        $parentCategory = $this->categoryFacade->create($categoryData);

        $categoryData->parent = $parentCategory;
        $this->categoryFacade->create($categoryData);

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $categoriesWithVisibleChildren = $this->categoryRepository->getCategoriesWithVisibleChildren([$parentCategory], self::FIRST_DOMAIN_ID);
        $this->assertCount(1, $categoriesWithVisibleChildren);
    }
}
