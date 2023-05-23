<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BrandResolverMap extends ResolverMap
{
    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Brand' => [
                'link' => function (Brand $brand) {
                    return $this->urlGenerator->generate(
                        'front_brand_detail',
                        ['id' => $brand->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    );
                },
                'seoTitle' => function (Brand $brand) {
                    return $brand->getSeoTitle($this->domain->getId());
                },
                'seoMetaDescription' => function (Brand $brand) {
                    return $brand->getSeoMetaDescription($this->domain->getId());
                },
                'seoH1' => function (Brand $brand) {
                    return $brand->getSeoH1($this->domain->getId());
                },
            ],
        ];
    }
}
