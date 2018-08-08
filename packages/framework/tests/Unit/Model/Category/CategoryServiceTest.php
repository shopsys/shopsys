<?php

namespace Tests\FrameworkBundle\Unit\Model\Category;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryService;

class CategoryServiceTest extends TestCase
{
    public function testCreateSetRoot(): void
    {
        $categoryData = new CategoryData();
        $rootCategory = new Category($categoryData);

        $categoryService = new CategoryService(new CategoryFactory());
        $category = $categoryService->create($categoryData, $rootCategory);

        $this->assertSame($rootCategory, $category->getParent());
    }

    public function testCreate(): void
    {
        $rootCategory = new Category(new CategoryData());
        $parentCategory = new Category(new CategoryData());
        $categoryData = new CategoryData();
        $categoryData->parent = $parentCategory;

        $categoryService = new CategoryService(new CategoryFactory());
        $category = $categoryService->create($categoryData, $rootCategory);

        $this->assertSame($parentCategory, $category->getParent());
    }

    public function testEditSetRoot(): void
    {
        $categoryData = new CategoryData();
        $rootCategory = new Category($categoryData);
        $category = new Category(new CategoryData());

        $categoryService = new CategoryService(new CategoryFactory());
        $categoryService->edit($category, $categoryData, $rootCategory);

        $this->assertSame($rootCategory, $category->getParent());
    }

    public function testEdit(): void
    {
        $rootCategory = new Category(new CategoryData());
        $parentCategory = new Category(new CategoryData());
        $categoryData = new CategoryData();
        $categoryData->parent = $parentCategory;
        $category = new Category(new CategoryData());

        $categoryService = new CategoryService(new CategoryFactory());
        $categoryService->edit($category, $categoryData, $rootCategory);

        $this->assertSame($parentCategory, $category->getParent());
    }
}
