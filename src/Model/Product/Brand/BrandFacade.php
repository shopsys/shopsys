<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade as BaseBrandFacade;

/**
 * @property \App\Model\Product\Brand\BrandRepository $brandRepository
 */
class BrandFacade extends BaseBrandFacade
{
    /**
     * @param string|null $searchText
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteBrands($searchText, $limit): PaginationResult
    {
        $page = 1;

        return $this->brandRepository->getPaginationResultForSearch(
            $searchText,
            $page,
            $limit
        );
    }
}
