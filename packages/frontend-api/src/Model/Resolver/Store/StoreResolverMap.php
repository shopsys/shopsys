<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Store;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Store' => [
                'slug' => fn (Store $store): string => $this->getSlug($store),
            ],
        ];
    }

    protected function getSlug(Store $store): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            'front_stores_detail',
            $store->getId(),
        );

        return '/' . $friendlyUrlSlug;
    }
}
