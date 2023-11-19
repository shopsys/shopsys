<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;

class GoPayClientFactory
{
    /**
     * @param mixed[] $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * @param string $locale
     * @return \App\Model\GoPay\GoPayClient
     */
    public function createByLocale(string $locale): GoPayClient
    {
        return new GoPayClient($this->getConfigByLocale($locale));
    }

    /**
     * @param string $locale
     * @return mixed[]
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
