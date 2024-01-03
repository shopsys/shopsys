<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Symfony\Component\Form\DataTransformerInterface;

class BlogCategoriesTypeTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]|null $blogCategories
     * @return bool[]
     */
    public function transform($blogCategories): array
    {
        $blogCategories = $blogCategories ?? [];
        $allBlogCategories = $this->blogCategoryFacade->getAllBlogCategoriesOfCollapsedTree($blogCategories);

        $isCheckedIndexedByBlogCategoryId = [];

        foreach ($allBlogCategories as $blogCategory) {
            $isChecked = in_array($blogCategory, $blogCategories, true);
            $isCheckedIndexedByBlogCategoryId[$blogCategory->getId()] = $isChecked;
        }

        return $isCheckedIndexedByBlogCategoryId;
    }

    /**
     * @param bool[]|null $isCheckedIndexedByBlogCategoryId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function reverseTransform($isCheckedIndexedByBlogCategoryId): array
    {
        $blogCategoryIds = [];

        foreach ($isCheckedIndexedByBlogCategoryId ?? [] as $blogCategoryId => $isChecked) {
            if ($isChecked) {
                $blogCategoryIds[] = $blogCategoryId;
            }
        }

        return $this->blogCategoryFacade->getByIds($blogCategoryIds);
    }
}
