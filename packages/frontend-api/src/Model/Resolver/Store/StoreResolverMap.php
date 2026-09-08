<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesQueryDtoFactory;

class StoreResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly DataLoaderInterface $storeSlugBatchLoader,
        protected readonly SeoAttributesQueryDtoFactory $seoAttributesQueryDtoFactory,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Store' => [
                'slug' => fn (Store $store) => $this->storeSlugBatchLoader->load($store->getId()),
                'seo' => fn (Store $store) => $this->seoAttributesQueryDtoFactory->createFromSeoAttributes(
                    $store->getSeoAttributes(),
                    $store->getDescription(),
                ),
            ],
        ];
    }
}
