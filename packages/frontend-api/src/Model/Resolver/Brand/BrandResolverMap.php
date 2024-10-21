<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Brand;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BrandResolverMap extends ResolverMap
{
    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
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
                'hreflangLinks' => function (Brand $brand) {
                    return $this->hreflangLinksFacade->getForBrand($brand, $this->domain->getId());
                },
                'slug' => function (Brand $brand) {
                    return $this->getSlug($brand);
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return string
     */
    protected function getSlug(Brand $brand): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            'front_brand_detail',
            $brand->getId(),
        );

        return '/' . $friendlyUrlSlug;
    }
}
