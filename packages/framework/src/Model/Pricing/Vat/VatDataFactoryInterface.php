<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

interface VatDataFactoryInterface
{
    public function create(): VatData;

    public function createFromVat(Vat $vat): VatData;
}
