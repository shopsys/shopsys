<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;

class GoPayClientFactory
{
    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\GoPay\GoPayClient
     */
    public function createByLocale(string $locale): GoPayClient
    {
        return new GoPayClient($this->getConfigByLocale($locale));
    }

    /**
     * @param string $locale
     * @return array
     */
    protected function getConfigByLocale(string $locale): array
    {
        if (!array_key_exists($locale, $this->config)) {
            throw new GoPayNotConfiguredException();
        }

        $configByLocale = $this->config[$locale];
        $this->config = array_merge($this->config, $configByLocale);

        if ($this->config['goid'] === null || $this->config['goid'] === '') {
            throw new GoPayNotConfiguredException();
        }

        return $this->config;
    }
}
