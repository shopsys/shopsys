<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Store;

use App\FrontendApi\Resolver\Store\Exception\StoreNotFoundUserError;
use App\Model\Store\Exception\StoreByUuidNotFoundException;
use App\Model\Store\Exception\StoreNotFoundException;
use App\Model\Store\Store;
use App\Model\Store\StoreFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

class StoreResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        StoreFacade $storeFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain
    ) {
        $this->storeFacade = $storeFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \App\Model\Store\Store
     */
    public function resolver(?string $uuid = null, ?string $urlSlug = null): Store
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
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'store',
        ];
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
