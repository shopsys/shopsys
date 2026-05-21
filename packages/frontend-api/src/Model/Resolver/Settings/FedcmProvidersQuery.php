<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigFactory;

class FedcmProvidersQuery extends AbstractQuery
{
    public function __construct(
        protected readonly SocialNetworkConfigFactory $socialNetworkConfigFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array<int, array{type: string, clientId: string, configUrl: string, autoSelect: bool}>
     */
    public function fedcmProvidersQuery(): array
    {
        return $this->socialNetworkConfigFactory->getEnabledFedcmProvidersForDomain($this->domain->getId());
    }
}
