<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="pricing_groups")
 * @ORM\Entity
 */
class PricingGroup
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @param int $domainId
     */
    public function __construct(PricingGroupData $pricingGroupData, $domainId)
    {
        $this->domainId = $domainId;
        $this->setData($pricingGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     */
    public function edit(PricingGroupData $pricingGroupData)
    {
        $this->setData($pricingGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     */
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
