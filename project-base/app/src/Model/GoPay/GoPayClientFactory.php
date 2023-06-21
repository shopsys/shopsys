<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;

class GoPayClientFactory
{
    /**
     * @param array $config
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
     * @return array
     */
    protected function getConfigByLocale(string $locale): array
    {
        $configByLocale = $this->config[$locale];
        $this->config = array_merge($this->config, $configByLocale);

        if ($this->config['goid'] === null) {
            throw new GoPayNotConfiguredException();
        }

        return $this->config;
    }
}
