<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CategoriesIdsToCategoriesTransformer implements DataTransformerInterface
{
    protected CategoryRepository $categoryRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[]|null $categories
     * @return int[]
     */
    public function transform($categories): array
    {
        $categoriesIds = [];

        if (is_iterable($categories)) {
            foreach ($categories as $category) {
                $categoriesIds[] = $category->getId();
            }
        }

        return $categoriesIds;
    }

    /**
     * @param int[] $categoriesIds
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function reverseTransform($categoriesIds): array
    {
        $categories = [];

        if (is_array($categoriesIds)) {
            foreach ($categoriesIds as $categoryId) {
                try {
                    $categories[] = $this->categoryRepository->getById($categoryId);
                } catch (CategoryNotFoundException $e) {
                    throw new TransformationFailedException('Category not found', 0, $e);
                }
            }
        }

        return $categories;
    }
}
