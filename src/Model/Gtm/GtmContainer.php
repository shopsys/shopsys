<?php

declare(strict_types=1);

namespace App\Model\Gtm;

use App\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GtmContainer
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var string|null
     */
    private $environment;

    /**
     * @var \App\Model\Gtm\DataLayer
     */
    private $dataLayer;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @param array $containersConfigs
     * @param \App\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        array $containersConfigs,
        Domain $domain,
        SessionInterface $session
    ) {
        $this->session = $session;

        try {
            $currentLocale = $domain->getLocale();
        } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException $e) {
            $currentLocale = $domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        }

        if (!array_key_exists($currentLocale, $containersConfigs)) {
            throw new \InvalidArgumentException(sprintf('Missing GTM configuration for "%s" domain id', $currentLocale));
        }

        $config = $containersConfigs[$currentLocale];
        $this->loadConfig($config);
    }

    /**
     * @param array $config
     */
    private function loadConfig(array $config): void
    {
        $configResolver = new OptionsResolver();
        $configResolver
            ->setRequired([
                'enabled',
            ])
            ->setRequired([
                'container_id',
            ])
            ->setRequired([
                'data_layer_locale',
            ])
            ->setDefined([
                'container_environment',
            ])
            ->setAllowedTypes('enabled', ['bool'])
            ->setAllowedTypes('container_id', ['null', 'string'])
            ->setAllowedTypes('data_layer_locale', ['string'])
            ->setAllowedTypes('container_environment', ['null', 'string'])
            ->resolve($config);

        $this->isEnabled = $config['enabled'];
        $this->id = $config['container_id'];
        $this->environment = $config['container_environment'];
        $this->dataLayer = new DataLayer($this->session, $config['data_layer_locale']);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string|null
     */
    public function getContainerId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContainerEnvironment(): ?string
    {
        return $this->environment;
    }

    /**
     * @return \App\Model\Gtm\DataLayer
     */
    public function getDataLayer(): DataLayer
    {
        return $this->dataLayer;
    }
}
