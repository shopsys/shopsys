<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="transport_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="transport_domain", columns={"transport_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class TransportDomain
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $transport;

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
    protected $enabled = false;

    public function __construct(Transport $transport, int $domainId)
    {
        $this->transport = $transport;
        $this->domainId = $domainId;
        $this->enabled = true;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
