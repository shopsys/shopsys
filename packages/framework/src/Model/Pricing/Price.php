<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

class Price
{
    /**
     * @var string
     */
    private $priceWithoutVat;

    /**
     * @var string
     */
    private $priceWithVat;

    /**
     * @var string
     */
    private $vatAmount;
    
    public function __construct(string $priceWithoutVat, string $priceWithVat)
    {
        $this->priceWithoutVat = $priceWithoutVat;
        $this->priceWithVat = $priceWithVat;
        $this->vatAmount = $priceWithVat - $priceWithoutVat;
    }

    public function getPriceWithoutVat(): string
    {
        return $this->priceWithoutVat;
    }

    public function getPriceWithVat(): string
    {
        return $this->priceWithVat;
    }

    public function getVatAmount(): string
    {
        return $this->vatAmount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $priceToAdd
     */
    public function add(self $priceToAdd): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return new self(
            $this->priceWithoutVat + $priceToAdd->getPriceWithoutVat(),
            $this->priceWithVat + $priceToAdd->getPriceWithVat()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $priceToSubtract
     */
    public function subtract(self $priceToSubtract): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return new self(
            $this->priceWithoutVat - $priceToSubtract->getPriceWithoutVat(),
            $this->priceWithVat - $priceToSubtract->getPriceWithVat()
        );
    }
}
