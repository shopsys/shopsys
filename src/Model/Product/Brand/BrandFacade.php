<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade as BaseBrandFacade;

/**
 * @property \App\Model\Product\Brand\BrandRepository $brandRepository
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Brand\BrandRepository $brandRepository, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFactoryInterface $brandFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
 * @method \App\Model\Product\Brand\Brand getById(int $brandId)
 * @method \App\Model\Product\Brand\Brand create(\App\Model\Product\Brand\BrandData $brandData)
 * @method \App\Model\Product\Brand\Brand edit(int $brandId, \App\Model\Product\Brand\BrandData $brandData)
 * @method \App\Model\Product\Brand\Brand[] getAll()
 * @method dispatchBrandEvent(\App\Model\Product\Brand\Brand $brand, string $eventType)
 * @method \App\Model\Product\Brand\Brand getByUuid(string $uuid)
 * @method \App\Model\Product\Brand\Brand[] getByUuids(string[] $uuids)
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

    /**
     * @param int[] $brandsIds
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function getBrandsByIds(array $brandsIds): array
    {
        return $this->brandRepository->getBrandsByIds($brandsIds);
    }
}
