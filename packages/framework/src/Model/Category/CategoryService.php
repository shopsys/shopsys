<?php

namespace Shopsys\FrameworkBundle\Model\Category;

class CategoryService
{

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFactoryInterface
     */
    protected $categoryFactory;

    public function __construct(CategoryFactoryInterface $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

    public function create(CategoryData $categoryData, Category $rootCategory): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $category = $this->categoryFactory->create($categoryData);
        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }

        return $category;
    }

    public function edit(Category $category, CategoryData $categoryData, Category $rootCategory): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $category->edit($categoryData);
        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }

        return $category;
    }

    public function setChildrenAsSiblings(Category $category): void
    {
        foreach ($category->getChildren() as $child) {
            $child->setParent($category->getParent());
        }
    }
}
