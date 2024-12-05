<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Convertim;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConvertimConfigProvider
{
    protected const string ENABLED = 'enabled';
    protected const string AUTHORIZATION_HEADER = 'authorizationHeader';
    protected const string PROJECT_UUID = 'projectUuid';
    protected const string IS_PRODUCTION_MODE = 'isProductionMode';
    protected const string CLIENT_ID = 'clientId';
    protected const string CLIENT_SECRET = 'clientSecret';

    /**
     * @var \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfig[]
     */
    protected array $configsByDomainId = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @param array $configsByDomainId
     */
    public function setConfigs(array $configsByDomainId): void
    {
        foreach ($configsByDomainId as $domainId => $config) {
            $configResolver = $this->createConfigResolver()
                ->resolve($config);

            $this->configsByDomainId[$domainId] = new ConvertimConfig(
                $configResolver[static::ENABLED],
                $configResolver[static::AUTHORIZATION_HEADER],
                $configResolver[static::PROJECT_UUID],
                $configResolver[static::IS_PRODUCTION_MODE],
                $configResolver[static::CLIENT_ID],
                $configResolver[static::CLIENT_SECRET],
            );
        }
    }

    /**
     * @return \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function createConfigResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setRequired([
                static::ENABLED,
                static::AUTHORIZATION_HEADER,
                static::PROJECT_UUID,
                static::IS_PRODUCTION_MODE,
                static::CLIENT_ID,
                static::CLIENT_SECRET,
            ])
            ->setAllowedTypes(static::ENABLED, ['bool'])
            ->setAllowedTypes(static::AUTHORIZATION_HEADER, ['string'])
            ->setAllowedTypes(static::PROJECT_UUID, ['string'])
            ->setAllowedTypes(static::IS_PRODUCTION_MODE, ['bool'])
            ->setAllowedTypes(static::CLIENT_ID, ['string'])
            ->setAllowedTypes(static::CLIENT_SECRET, ['string']);
    }

    /**
     * @return \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfig
     */
    public function getConfigForCurrentDomain(): ConvertimConfig
    {
        if (!array_key_exists($this->domain->getId(), $this->configsByDomainId)) {
            throw new InvalidArgumentException(sprintf('Missing Convertim configuration for domain with ID: "%d"', $this->domain->getId()));
        }

        return $this->configsByDomainId[$this->domain->getId()];
    }
}
