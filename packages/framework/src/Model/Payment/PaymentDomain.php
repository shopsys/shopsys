<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="payment_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="payment_domain", columns={"payment_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class PaymentDomain
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
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $payment;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled;
    
    public function __construct(Payment $payment, int $domainId)
    {
        $this->payment = $payment;
        $this->domainId = $domainId;
        $this->enabled = true;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
