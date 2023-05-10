<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Store\Store;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StoreResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(FriendlyUrlFacade $friendlyUrlFacade, Domain $domain)
    {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    protected function map()
    {
        return [
            'Store' => [
                'slug' => function (Store $store) {
                    return $this->getSlug($store);
                },
                'openingHoursHtml' => function (Store $store) {
                    return $store->getOpeningHours() !== null ? nl2br($store->getOpeningHours()) : null;
                },
            ],
        ];
    }

    /**
     * @param \App\Model\Store\Store $store
     * @return string
     */
    private function getSlug(Store $store): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            'front_stores_detail',
            $store->getId()
        );

        return '/' . $friendlyUrlSlug;
    }
}
