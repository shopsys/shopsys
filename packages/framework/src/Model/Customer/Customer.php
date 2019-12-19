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
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     */
    public function addBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddresses = new ArrayCollection();
        $this->billingAddresses->add($billingAddress);
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
}
