<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use App\FrontendApi\Resolver\Store\Exception\StoreNotFoundUserError;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\Exception\StoreNotFoundException;
use App\Model\Store\Store;
use App\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class StoreQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly StoreFacade $storeFacade,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly Domain $domain
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Store\Store
     */
    public function storeQuery(?string $uuid = null, ?string $urlSlug = null): Store
    {
        if ($uuid !== null) {
            try {
                return $this->storeFacade->getByUuidEnabledOnDomain($uuid, $this->domain->getId());
            } catch (StoreByUuidNotFoundException $storeNotFoundException) {
                throw new StoreNotFoundUserError($storeNotFoundException->getMessage());
            }
        }

        if ($urlSlug !== null) {
            $urlSlug = ltrim($urlSlug, '/');

            return $this->getVisibleByDomainIdAndSlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param string $urlSlug
     * @return \App\Model\Store\Store
     */
    protected function getVisibleByDomainIdAndSlug(string $urlSlug): Store
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_stores_detail',
                $urlSlug
            );

            return $this->storeFacade->getByIdEnabledOnDomain($friendlyUrl->getEntityId(), $this->domain->getId());
        } catch (FriendlyUrlNotFoundException|StoreNotFoundException $exception) {
            throw new StoreNotFoundUserError(sprintf('Store with URL slug "%s" does not exist.', $urlSlug));
        }
    }
}
