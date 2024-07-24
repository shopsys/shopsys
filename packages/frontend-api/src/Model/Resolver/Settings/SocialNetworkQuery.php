<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Hybridauth\Hybridauth;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigFactory;

class SocialNetworkQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkConfigFactory $socialNetworkConfigFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly SocialNetworkConfigFactory $socialNetworkConfigFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return string[]
     */
    public function socialNetworkLoginConfigQuery(): array
    {
        $socialNetworkLoginConfig = $this->socialNetworkConfigFactory->createConfigForDomain($this->domain->getId());

        return (new Hybridauth($socialNetworkLoginConfig))->getProviders();
    }
}
