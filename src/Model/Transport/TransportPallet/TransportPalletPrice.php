<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPallet;

use App\Model\Transport\Transport;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Table(name="transport_pallet_prices")
 * @ORM\Entity
 */
class TransportPalletPrice
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Model\Transport\Transport
     * @ORM\ManyToOne(targetEntity="App\Model\Transport\Transport")Version20200923055405
     */
    private Transport $transport;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private int $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     *
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    private ?Money $productsPriceTo;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     *
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    private Money $transportPrice;

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $productsPriceTo
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $transportPrice
     */
    public function __construct(Transport $transport, int $domainId, ?Money $productsPriceTo, Money $transportPrice)
    {
        $this->transport = $transport;
        $this->domainId = $domainId;
        $this->productsPriceTo = $productsPriceTo;
        $this->transportPrice = $transportPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getProductsPriceTo(): ?Money
    {
        return $this->productsPriceTo;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getTransportPrice(): Money
    {
        return $this->transportPrice;
    }

    public function setProductsPriceToAsNull(): void
    {
        $this->productsPriceTo = null;
    }
}
