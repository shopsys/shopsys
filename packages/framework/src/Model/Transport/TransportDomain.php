<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

#[ORM\Table(name: 'transport_domains')]
#[ORM\UniqueConstraint(name: 'transport_domain', columns: ['transport_id', 'domain_id'])]
#[ORM\Entity]
class TransportDomain
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Transport::class, inversedBy: 'domains')]
    protected $transport;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $enabled = false;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Vat::class)]
    protected $vat;

    public function __construct(Transport $transport, int $domainId, Vat $vat)
    {
        $this->transport = $transport;
        $this->domainId = $domainId;
        $this->vat = $vat;
        $this->enabled = true;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function setVat($vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVat()
    {
        return $this->vat;
    }
}
