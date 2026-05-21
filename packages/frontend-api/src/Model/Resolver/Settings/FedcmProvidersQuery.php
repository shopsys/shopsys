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
     * @return array<int, array{type: string, clientId: string, configUrl: string, autoSelect: bool, params: array<int, array{name: string, value: string}>}>
     */
    public function fedcmProvidersQuery(): array
    {
        $providers = $this->socialNetworkConfigFactory->getEnabledFedcmProvidersForDomain($this->domain->getId());

        return array_map(
            static function (array $provider): array {
                $params = [];

                foreach ($provider['params'] as $name => $value) {
                    $params[] = ['name' => (string)$name, 'value' => (string)$value];
                }

                return [
                    'type' => $provider['type'],
                    'clientId' => $provider['clientId'],
                    'configUrl' => $provider['configUrl'],
                    'autoSelect' => $provider['autoSelect'],
                    'params' => $params,
                ];
            },
            $providers,
        );
    }
}
