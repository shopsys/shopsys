<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

class VatService
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $vatData)
    {
        return new Vat($vatData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function edit(Vat $vat, VatData $vatData)
    {
        $vat->edit($vatData);

        return $vat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $defaultVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatToDelete
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
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
