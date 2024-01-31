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
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\BillingAddress>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Customer\BillingAddress", mappedBy="customer", cascade={"persist", "remove"})
     */
    protected $billingAddresses;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress", mappedBy="customer", cascade={"persist", "remove"})
     */
    protected $deliveryAddresses;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     */
    public function __construct(CustomerData $customerData)
    {
        $this->domainId = $customerData->domainId;
        $this->setData($customerData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     */
    public function edit(CustomerData $customerData): void
    {
        $this->setData($customerData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     */
    protected function setData(CustomerData $customerData): void
    {
        $this->billingAddresses = new ArrayCollection();
        $this->deliveryAddresses = new ArrayCollection();

        if ($customerData->billingAddress !== null) {
            $this->addBillingAddress($customerData->billingAddress);
        }

        foreach ($customerData->deliveryAddresses as $deliveryAddress) {
            $this->addDeliveryAddress($deliveryAddress);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     */
    protected function addBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddresses = new ArrayCollection();
        $this->billingAddresses->add($billingAddress);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     */
    protected function addDeliveryAddress(DeliveryAddress $deliveryAddress): void
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public function getDeliveryAddresses(): array
    {
        return $this->deliveryAddresses->getValues();
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }
}
