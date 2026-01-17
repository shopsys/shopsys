<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;

/**
 * @ORM\Table(name="withdrawal_requests")
 * @ORM\Entity
 */
class WithdrawalRequest
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     * @ORM\OneToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $requestedAt;

    public function __construct(Order $order, WithdrawalRequestData $withdrawalRequestData)
    {
        $this->order = $order;
        $this->setData($withdrawalRequestData);
    }

    protected function setData(WithdrawalRequestData $withdrawalRequestData): void
    {
        $this->firstName = $withdrawalRequestData->firstName;
        $this->lastName = $withdrawalRequestData->lastName;
        $this->telephone = $withdrawalRequestData->telephone;
        $this->email = $withdrawalRequestData->email;
        $this->note = $withdrawalRequestData->note;
        $this->requestedAt = $withdrawalRequestData->requestedAt ?? new DateTimeImmutable();
    }

    public function edit(WithdrawalRequestData $withdrawalRequestData): void
    {
        $this->setData($withdrawalRequestData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getRequestedAt()
    {
        return $this->requestedAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
