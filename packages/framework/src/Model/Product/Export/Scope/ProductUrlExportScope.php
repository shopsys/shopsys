<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class ProductUrlExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly HreflangLinksFacade $hreflangLinksFacade,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly FriendlyUrlRepository $friendlyUrlRepository,
    )
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'detail_url' => $this->extractDetailUrl($domainId, $object),
            'hreflang_links' => $this->hreflangLinksFacade->getForProduct($object, $domainId),
        ];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    protected function extractDetailUrl(int $domainId, Product $product): string
    {
        $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl(
            $domainId,
            'front_product_detail',
            $product->getId(),
        );

        return $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
    }
}
