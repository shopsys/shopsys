<?php

declare(strict_types=1);

namespace App\Twig;

use App\Component\Domain\Domain;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        Domain $domain,
        CategoryFacade $categoryFacade
    ) {
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCategoriesLookingLikeChildren', [$this, 'getCategoriesLookingLikeChildren']),
        ];
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @return \App\Model\Category\Category[]
     */
    public function getCategoriesLookingLikeChildren(Category $parentCategory): array
    {
        return $this->categoryFacade->getVisibleCategoriesLookingLikeChildren($parentCategory, $this->domain->getId());
    }
}
