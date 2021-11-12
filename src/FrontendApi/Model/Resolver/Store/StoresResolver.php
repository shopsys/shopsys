<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Store;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StoresResolver implements ResolverInterface, AliasedInterface
{
    private const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        StoreFacade $storeFacade,
        Domain $domain
    ) {
        $this->storeFacade = $storeFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolver(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $domainId = $this->domain->getId();

        $paginator = new Paginator(function ($offset, $limit) use ($domainId) {
            return $this->storeFacade->getStoresListEnabledOnDomain($domainId, $limit, $offset);
        });

        $storesCount = $this->storeFacade->getStoresCountEnabledOnDomain($domainId);
        return $paginator->auto($argument, $storesCount);
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object|null
     */
    public function resolverByTransport(Transport $transport, Argument $argument)
    {
        if ($transport->isPersonalPickup()) {
            return $this->resolver($argument);
        }

        return null;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'stores',
            'resolverByTransport' => 'storesByTransport',
        ];
    }
}
