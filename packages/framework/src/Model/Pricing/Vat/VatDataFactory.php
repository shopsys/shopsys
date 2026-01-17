<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatDataFactory
{
    protected function createInstance(): VatData
    {
        return new VatData();
    }

    public function create(): VatData
    {
        return $this->createInstance();
    }

    public function createFromVat(Vat $vat): VatData
    {
        $vatData = $this->createInstance();
        $this->fillFromVat($vatData, $vat);

        return $vatData;
    }

    protected function fillFromVat(VatData $vatData, Vat $vat)
    {
        $vatData->name = $vat->getName();
        $vatData->percent = $vat->getPercent();
    }
}
