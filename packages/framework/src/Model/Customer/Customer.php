<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[Loggable]
#[ORM\Table(name: 'customers')]
#[ORM\Entity]
class Customer implements DomainSeparatedEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\BillingAddress>
     */
    #[ORM\OneToMany(targetEntity: BillingAddress::class, mappedBy: 'customer', cascade: ['persist', 'remove'])]
    protected $billingAddresses;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress>
     */
    #[ORM\OneToMany(targetEntity: DeliveryAddress::class, mappedBy: 'customer', cascade: ['persist', 'remove'])]
    protected $deliveryAddresses;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    public function __construct(CustomerData $customerData)
    {
        $this->domainId = $customerData->domainId;
        $this->setData($customerData);
    }

    public function edit(CustomerData $customerData): void
    {
        $this->setData($customerData);
    }

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

    protected function addBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddresses = new ArrayCollection();
        $this->billingAddresses->add($billingAddress);
    }

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
     * A customer of an individual has no name, the name of the person is in the records of its users
     */
    #[EntityLogIdentify]
    public function getEntityLogIdentifier(): string
    {
        return $this->isCompanyCustomer() ? (string)$this->getBillingAddress()->getCompanyName() : (string)$this->id;
    }

    public function getBillingAddress(): BillingAddress
    {
        return $this->billingAddresses->first();
    }

    public function isCompanyCustomer(): bool
    {
        return $this->getBillingAddress()->isCompanyCustomer();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress[]
     */
    public function getDeliveryAddresses()
    {
        return $this->deliveryAddresses->getValues();
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }
}
