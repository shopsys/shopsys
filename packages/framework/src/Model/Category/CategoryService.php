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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function create(CategoryData $categoryData, Category $rootCategory)
    {
        $category = $this->categoryFactory->create($categoryData);
        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }

        return $category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function edit(Category $category, CategoryData $categoryData, Category $rootCategory)
    {
        $category->edit($categoryData);
        if ($category->getParent() === null) {
            $category->setParent($rootCategory);
        }

        return $category;
    }

    public function setChildrenAsSiblings(Category $category)
    {
        foreach ($category->getChildren() as $child) {
            $child->setParent($category->getParent());
        }
    }
}
