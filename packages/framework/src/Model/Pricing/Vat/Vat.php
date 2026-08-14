<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'vats')]
#[ORM\Entity]
class Vat implements DomainSeparatedEntityInterface
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
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50)]
    protected $name;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    protected $percent;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class)]
    protected $replaceWith;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    public function __construct(VatData $vatData, int $domainId)
    {
        $this->percent = $vatData->percent;
        $this->domainId = $domainId;
        $this->setData($vatData);
    }

    public function edit(VatData $vatData): void
    {
        $this->setData($vatData);
    }

    protected function setData(VatData $vatData): void
    {
        $this->name = $vatData->name;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function getReplaceWith()
    {
        return $this->replaceWith;
    }

    public function markForDeletion(self $newVat): void
    {
        $this->replaceWith = $newVat;
    }

    /**
     * @return bool
     */
    public function isMarkedAsDeleted()
    {
        return $this->replaceWith !== null;
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
