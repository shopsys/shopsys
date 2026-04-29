<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CategoriesIdsToCategoriesTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly CategoryFacade $categoryFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[]|null $categories
     * @return int[]
     */
    #[Override]
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
     * @param string[] $categoriesIds
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    #[Override]
    public function reverseTransform($categoriesIds): array
    {
        $categories = [];

        if (is_array($categoriesIds)) {
            foreach ($categoriesIds as $categoryId) {
                try {
                    $categories[] = $this->categoryFacade->getById((int)$categoryId);
                } catch (CategoryNotFoundException $e) {
                    throw new TransformationFailedException('Category not found', 0, $e);
                }
            }
        }

        return $categories;
    }
}
