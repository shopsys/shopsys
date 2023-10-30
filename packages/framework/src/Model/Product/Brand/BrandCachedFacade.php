<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Contracts\Service\ResetInterface;

class BrandCachedFacade implements ResetInterface
{
    /**
     * @var array<int, array<int, string>>
     */
    protected array $brandUrlsIndexedByBrandIdAndDomainId = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(protected readonly FriendlyUrlFacade $friendlyUrlFacade)
    {
    }

    /**
     * @param int $brandId
     * @param int $domainId
     * @return string
     */
    public function getBrandUrlByDomainId(int $brandId, int $domainId): string
    {
        if (
            !array_key_exists($brandId, $this->brandUrlsIndexedByBrandIdAndDomainId)
            || !array_key_exists($domainId, $this->brandUrlsIndexedByBrandIdAndDomainId[$brandId])
        ) {
            $this->brandUrlsIndexedByBrandIdAndDomainId[$brandId][$domainId] = $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $domainId,
                'front_brand_detail',
                $brandId,
            );
        }

        return $this->brandUrlsIndexedByBrandIdAndDomainId[$brandId][$domainId];
    }

    public function reset(): void
    {
        $this->brandUrlsIndexedByBrandIdAndDomainId = [];
    }
}
