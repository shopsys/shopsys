<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

use App\Model\Transport\Transport;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Table(name="transport_packages")
 * @ORM\Entity
 */
class TransportPackage
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Model\Transport\Transport
     * @ORM\ManyToOne(targetEntity="App\Model\Transport\Transport", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transport;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxProductPackagesCount;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $maxWeight;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    private $priceWithVat;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxGirth;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dimension1;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dimension2;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dimension3;

    /**
     * @param \App\Model\Transport\TransportPackage\TransportPackageData $transportPackageData
     * @param \App\Model\Transport\Transport $transport
     */
    public function __construct(TransportPackageData $transportPackageData, Transport $transport)
    {
        $this->edit($transportPackageData);
        $this->transport = $transport;
    }

    /**
     * @param \App\Model\Transport\TransportPackage\TransportPackageData $transportPackageData
     */
    public function edit(TransportPackageData $transportPackageData): void
    {
        $this->domainId = $transportPackageData->domainId;
        $this->priceWithVat = $transportPackageData->priceWithVat;
        $this->maxProductPackagesCount = $transportPackageData->maxProductPackagesCount;
        $this->maxWeight = $transportPackageData->maxWeight;
        $this->maxGirth = $transportPackageData->maxGirth;
        if ($transportPackageData->dimension1 !== null && $transportPackageData->dimension2 !== null && $transportPackageData->dimension3 !== null) {
            $dimensions = [$transportPackageData->dimension1, $transportPackageData->dimension2, $transportPackageData->dimension3];
            sort($dimensions, SORT_NUMERIC);
            $this->dimension1 = $dimensions[2];
            $this->dimension2 = $dimensions[1];
            $this->dimension3 = $dimensions[0];
        } else {
            $this->dimension1 = null;
            $this->dimension2 = null;
            $this->dimension3 = null;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Transport\Transport
     */
    public function getTransport(): Transport
    {
        return $this->transport;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return int|null
     */
    public function getMaxProductPackagesCount(): ?int
    {
        return $this->maxProductPackagesCount;
    }

    /**
     * @return int
     */
    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithVat(): Money
    {
        return $this->priceWithVat;
    }

    /**
     * @return int|null
     */
    public function getMaxGirth(): ?int
    {
        return $this->maxGirth;
    }

    /**
     * @return int|null
     */
    public function getDimension1(): ?int
    {
        return $this->dimension1;
    }

    /**
     * @return int|null
     */
    public function getDimension2(): ?int
    {
        return $this->dimension2;
    }

    /**
     * @return int|null
     */
    public function getDimension3(): ?int
    {
        return $this->dimension3;
    }
}
