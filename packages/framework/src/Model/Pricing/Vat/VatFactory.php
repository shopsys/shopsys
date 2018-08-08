<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatFactory implements VatFactoryInterface
{
    public function create(VatData $data): Vat
    {
        return new Vat($data);
    }
}
