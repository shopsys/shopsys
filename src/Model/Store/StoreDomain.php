<?php

declare(strict_types=1);

namespace App\Model\Store;

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
    protected int $id;

    /**
     * @var \App\Model\Store\Store
     * @ORM\ManyToOne(targetEntity="\App\Model\Store\Store", inversedBy="domains", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected Store $store;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected bool $isEnabled;

    /**
     * @param \App\Model\Store\Store $store
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
