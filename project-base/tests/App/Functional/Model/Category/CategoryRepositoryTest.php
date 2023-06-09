<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Category;

use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class CategoryRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @inject
     */
    private CategoryFacade $categoryFacade;

    /**
     * @inject
     */
    private CategoryRepository $categoryRepository;

    /**
     * @inject
     */
    private CategoryVisibilityRepository $categoryVisibilityRepository;

    /**
     * @inject
     */
    private CategoryDataFactoryInterface $categoryDataFactory;

    /**
     * @inject
     */
    private Localization $localization;

    public function testDoNotGetCategoriesWithoutVisibleChildren()
    {
        $categoryData = $this->categoryDataFactory->create();
        $names = [];
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $names[$locale] = 'name';
        }
        $categoryData->name = $names;
        /** @var \App\Model\Category\Category $rootCategory */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData->parent = $rootCategory;

        /** @var \App\Model\Category\Category $parentCategory */
        $parentCategory = $this->categoryFacade->create($categoryData);

        $categoryData->enabled[self::FIRST_DOMAIN_ID] = false;
        $categoryData->enabled[self::SECOND_DOMAIN_ID] = false;

        $categoryData->parent = $parentCategory;
        $this->categoryFacade->create($categoryData);

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $categoriesWithVisibleChildren = $this->categoryRepository->getCategoriesWithVisibleChildren(
            [$parentCategory],
            self::FIRST_DOMAIN_ID
        );
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
        /** @var \App\Model\Category\Category $rootCategory */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData->parent = $rootCategory;

        /** @var \App\Model\Category\Category $parentCategory */
        $parentCategory = $this->categoryFacade->create($categoryData);

        $categoryData->parent = $parentCategory;
        $this->categoryFacade->create($categoryData);

        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $categoriesWithVisibleChildren = $this->categoryRepository->getCategoriesWithVisibleChildren(
            [$parentCategory],
            self::FIRST_DOMAIN_ID
        );
        $this->assertCount(1, $categoriesWithVisibleChildren);
    }
}
