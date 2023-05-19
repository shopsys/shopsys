<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

interface VatFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $data
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $data, int $domainId): Vat;
}
