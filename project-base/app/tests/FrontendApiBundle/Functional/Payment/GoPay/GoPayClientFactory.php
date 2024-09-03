<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory as BaseGoPayClientFactory;

class GoPayClientFactory extends BaseGoPayClientFactory
{
    /**
     * @param array $gopayConfig
     * @return \Tests\FrontendApiBundle\Functional\Payment\GoPay\GoPayClient
     */
    protected function createInstance(array $gopayConfig): GoPayClient
    {
        return new GoPayClient($gopayConfig);
    }
}
