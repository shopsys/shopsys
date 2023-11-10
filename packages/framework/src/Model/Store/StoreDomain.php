<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="store_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="store_domain", columns={"store_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class StoreDomain
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\Store
     * @ORM\ManyToOne(targetEntity="\Shopsys\FrameworkBundle\Model\Store\Store", inversedBy="domains", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $store;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isEnabled;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @param int $domainId
     */
    public function __construct(Store $store, int $domainId)
    {
        $this->store = $store;
        $this->domainId = $domainId;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     */
    public function setEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }
}
