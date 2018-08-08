<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatDataFactory implements VatDataFactoryInterface
{
    public function create(): VatData
    {
        return new VatData();
    }

    public function createFromVat(Vat $vat): VatData
    {
        $vatData = new VatData();
        $this->fillFromVat($vatData, $vat);

        return $vatData;
    }

    protected function fillFromVat(VatData $vatData, Vat $vat): void
    {
        $vatData->name = $vat->getName();
        $vatData->percent = $vat->getPercent();
    }
}
