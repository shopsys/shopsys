<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'pricing_groups')]
#[ORM\Entity]
class PricingGroup
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
    #[ORM\Column(type: 'text')]
    protected $name;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @param int $domainId
     */
    public function __construct(PricingGroupData $pricingGroupData, $domainId)
    {
        $this->domainId = $domainId;
        $this->setData($pricingGroupData);
    }

    public function edit(PricingGroupData $pricingGroupData): void
    {
        $this->setData($pricingGroupData);
    }

    protected function setData(PricingGroupData $pricingGroupData): void
    {
        $this->name = $pricingGroupData->name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }
}
