<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory as BaseGoPayClientFactory;

class GoPayClientFactory extends BaseGoPayClientFactory
{
    /**
     * @param string $locale
     * @return \Tests\FrontendApiBundle\Functional\Payment\GoPay\GoPayClient
     */
    public function createByLocale(string $locale): GoPayClient
    {
        return new GoPayClient($this->getConfigByLocale($locale));
    }
}
