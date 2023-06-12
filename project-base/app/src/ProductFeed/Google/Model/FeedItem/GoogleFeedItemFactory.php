<?php

declare(strict_types=1);

namespace App\ProductFeed\Google\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFactory as BaseGoogleFeedItemFactory;

class GoogleFeedItemFactory extends BaseGoogleFeedItemFactory
{
    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem
     */
    public function create(Product $product, DomainConfig $domainConfig): GoogleFeedItem
    {
        return new GoogleFeedItem(
            $product->getId(),
            $product->getFullname($domainConfig->getLocale()),
            $product->getCalculatedSellingDenied(),
            $this->getPrice($product, $domainConfig),
            $this->getCurrency($domainConfig),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->getBrandName($product),
            $product->getDescription($domainConfig->getId()),
            $product->getEan(),
            $product->getPartno(),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
        );
    }
}
