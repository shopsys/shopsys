<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @property \App\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection $payments
 * @method \App\Model\Payment\Payment[] getPayments()
 * @method setTranslations(\App\Model\Transport\TransportData $transportData)
 * @method setDomains(\App\Model\Transport\TransportData $transportData)
 * @method createDomains(\App\Model\Transport\TransportData $transportData)
 * @method addPayment(\App\Model\Payment\Payment $payment)
 * @method setPayments(\App\Model\Payment\Payment[] $payments)
 * @method removePayment(\App\Model\Payment\Payment $payment)
 */
class Transport extends BaseTransport
{
    /**
     * @var \App\Model\Product\Type\ProductType[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Type\ProductType")
     */
    private $productTypes;

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function __construct(BaseTransportData $transportData)
    {
        parent::__construct($transportData);
        $this->productTypes = new ArrayCollection($transportData->productTypes);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function edit(BaseTransportData $transportData)
    {
        parent::edit($transportData);
        $this->editProductTypes($transportData->productTypes);
    }

    /**
     * @param \App\Model\Product\Type\ProductType[] $productTypes
     */
    private function editProductTypes(array $productTypes): void
    {
        $this->productTypes->clear();
        foreach ($productTypes as $productType) {
            $this->productTypes->add($productType);
        }
    }

    /**
     * @return \App\Model\Product\Type\ProductType[]
     */
    public function getProductTypes(): array
    {
        return $this->productTypes->toArray();
    }
}
