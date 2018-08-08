<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

interface VatFactoryInterface
{

    public function create(VatData $data): Vat;
}
