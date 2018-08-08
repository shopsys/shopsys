<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatService
{

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getNewDefaultVat(Vat $defaultVat, Vat $vatToDelete, Vat $newVat)
    {
        if ($defaultVat->getId() === $vatToDelete->getId()) {
            return $newVat;
        }
        return $defaultVat;
    }
}
