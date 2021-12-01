<?php

declare(strict_types=1);

namespace App\Model\Gtm;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     * @var array
     */
    private $containersConfigs;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var bool
     */
    private $isConfigLoaded = false;

    /**
     * @param array $containersConfigs
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        array $containersConfigs,
        Domain $domain,
        SessionInterface $session
    ) {
        $this->containersConfigs = $containersConfigs;
        $this->session = $session;
        $this->domain = $domain;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $currentLocale = $this->domain->getLocale();

        if (!array_key_exists($this->domain->getLocale(), $this->containersConfigs)) {
            throw new InvalidArgumentException(sprintf('Missing GTM configuration for "%s" domain id', $currentLocale));
        }

        $config = $this->containersConfigs[$currentLocale];
        $this->loadConfig($config);

        return $this;
    }

    /**
     * @param array $config
     */
    private function loadConfig(array $config): void
    {
        if ($this->isConfigLoaded) {
            return;
        }

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

        $this->isConfigLoaded = true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $this->init();

        return $this->isEnabled;
    }

    /**
     * @return string|null
     */
    public function getContainerId(): ?string
    {
        $this->init();

        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContainerEnvironment(): ?string
    {
        $this->init();

        return $this->environment;
    }

    /**
     * @return \App\Model\Gtm\DataLayer
     */
    public function getDataLayer(): DataLayer
    {
        $this->init();

        return $this->dataLayer;
    }
}
