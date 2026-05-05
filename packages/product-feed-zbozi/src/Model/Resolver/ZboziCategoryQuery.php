<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Resolver;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory\ZboziCategoryFacade;

class ZboziCategoryQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ZboziCategoryFacade $zboziCategoryFacade,
    ) {
    }

    public function zboziCategoryByCategoryQuery(Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix): ?string
    {
        $category = $categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix
            ? $categoryOrReadyCategorySeoMix->getCategory()
            : $categoryOrReadyCategorySeoMix;

        return $this->zboziCategoryFacade->findFullNameByCategoryIdAndLocale(
            $category->getId(),
            $this->domain->getLocale(),
        );
    }
}
