<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Product\Type\ProductType;
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
    public const TYPE_COMMON = 'common';
    public const TYPE_PACKAGE = 'package';
    public const TYPE_PALLET = 'pallet';

    /**
     * @var \App\Model\Product\Type\ProductType[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Product\Type\ProductType")
     */
    private $productTypes;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $personalPickup;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isOverLimitTransport;

    /**
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    private int $externalId;

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function __construct(BaseTransportData $transportData)
    {
        parent::__construct($transportData);
        $this->productTypes = new ArrayCollection($transportData->productTypes);
        $this->personalPickup = $transportData->personalPickup;
        $this->type = $transportData->type;
        $this->isOverLimitTransport = $transportData->isOverLimitTransport;
        $this->externalId = $transportData->externalId;
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    public function edit(BaseTransportData $transportData)
    {
        parent::edit($transportData);
        $this->editProductTypes($transportData->productTypes);
        $this->personalPickup = $transportData->personalPickup;
        $this->type = $transportData->type;
        $this->isOverLimitTransport = $transportData->isOverLimitTransport;
        $this->externalId = $transportData->externalId;
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

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @return bool
     */
    public function hasProductType(ProductType $productType): bool
    {
        return $this->productTypes->contains($productType);
    }

    /**
     * @return bool
     */
    public function isPersonalPickup(): bool
    {
        return $this->personalPickup;
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    protected function setData(BaseTransportData $transportData): void
    {
        parent::setData($transportData);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isOverLimitTransport(): bool
    {
        return $this->isOverLimitTransport;
    }

    /**
     * @return int
     */
    public function getExternalId(): int
    {
        return $this->externalId;
    }
}
