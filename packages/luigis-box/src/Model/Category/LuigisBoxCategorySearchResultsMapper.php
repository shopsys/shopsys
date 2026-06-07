<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;

class LuigisBoxCategorySearchResultsMapper
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function mapCategoryData(LuigisBoxResult $luigisBoxResult): array
    {
        $categoryArray = $this->categoryFacade->getVisibleCategoriesByIds([$luigisBoxResult->getIds()], $this->domain->getCurrentDomainConfig());

        return array_first($categoryArray);
    }
}
