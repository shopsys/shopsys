<?php

declare(strict_types = 1);

namespace App\Model\GoPay;

class GoPayClientFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
    private function getConfigByLocale(string $locale): array
    {
        $configByLocale = $this->config[$locale];
        $this->config = array_merge($this->config, $configByLocale);

        if ($this->config['goid'] === null) {
            throw new \App\Model\GoPay\Exception\GoPayNotConfiguredException();
        }

        return $this->config;
    }
}
