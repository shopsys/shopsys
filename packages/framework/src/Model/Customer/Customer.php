<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="customers")
 * @ORM\Entity
 */
class Customer
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddress[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Customer\BillingAddress", mappedBy="customer", cascade={"persist"})
     */
    protected $billingAddresses;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress", mappedBy="customer", cascade={"persist"})
     */
    protected $deliveryAddresses;

    public function __construct()
    {
        $this->billingAddresses = new ArrayCollection();
        $this->deliveryAddresses = new ArrayCollection();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     */
    public function addBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddresses = new ArrayCollection();
        $this->billingAddresses->add($billingAddress);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     */
    public function addDeliveryAddress(DeliveryAddress $deliveryAddress): void
    {
        $this->deliveryAddresses->add($deliveryAddress);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function getBillingAddress(): BillingAddress
    {
        return $this->billingAddresses->first();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function getDeliveryAddress(): ?DeliveryAddress
    {
        if (count($this->deliveryAddresses) > 0) {
            return $this->deliveryAddresses->first();
        }

        return null;
    }
}
