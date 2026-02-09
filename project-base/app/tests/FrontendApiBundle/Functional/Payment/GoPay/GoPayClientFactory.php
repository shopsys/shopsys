<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use Override;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory as BaseGoPayClientFactory;

class GoPayClientFactory extends BaseGoPayClientFactory
{
    #[Override]
    protected function createInstance(array $gopayConfig): GoPayClient
    {
        return new GoPayClient($gopayConfig);
    }
}
