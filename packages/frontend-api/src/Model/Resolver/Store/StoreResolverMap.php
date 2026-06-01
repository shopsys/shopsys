<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly DataLoaderInterface $storeSlugBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Store' => [
                'slug' => fn (Store $store) => $this->storeSlugBatchLoader->load($store->getId()),
            ],
        ];
    }
}
