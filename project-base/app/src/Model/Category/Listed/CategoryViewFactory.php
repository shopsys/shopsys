<?php

declare(strict_types=1);

namespace App\Model\Category\Listed;

use App\Model\Category\Category;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategoryViewFactory
{
    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $generator
     */
    public function __construct(
        private UrlGeneratorInterface $generator,
    ) {
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Category\Listed\CategoryView
     */
    public function createFromCategory(Category $category): CategoryView
    {
        return new CategoryView(
            $category->getName(),
            $this->generator->generate('front_product_list', ['id' => $category->getId()]),
        );
    }
}
