<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatService
{

    public function getNewDefaultVat(Vat $defaultVat, Vat $vatToDelete, Vat $newVat): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        if ($defaultVat->getId() === $vatToDelete->getId()) {
            return $newVat;
        }
        return $defaultVat;
    }
}
