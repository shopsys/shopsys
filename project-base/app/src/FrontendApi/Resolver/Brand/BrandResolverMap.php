<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Brand;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandResolverMap as BaseBrandResolverMap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BrandResolverMap extends BaseBrandResolverMap
{
    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Domain $domain,
        private FriendlyUrlFacade $friendlyUrlFacade,
    ) {
        parent::__construct($urlGenerator, $domain);
    }

    /**
     * @return mixed[]
     */
    protected function map(): array
    {
        $map = parent::map();

        $map['Brand']['slug'] = function (Brand $brand) {
            return $this->getSlug($brand);
        };

        return $map;
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @return string
     */
    private function getSlug(Brand $brand): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            'front_brand_detail',
            $brand->getId(),
        );

        return '/' . $friendlyUrlSlug;
    }
}
