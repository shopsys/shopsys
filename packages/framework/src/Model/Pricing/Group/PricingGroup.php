<?php

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
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    protected $coefficient;

    /**
     * @param int $domainId
     */
    public function __construct(PricingGroupData $pricingGroupData, $domainId)
    {
        $this->coefficient = $pricingGroupData->coefficient;
        $this->name = $pricingGroupData->name;
        $this->domainId = $domainId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getCoefficient(): string
    {
        return $this->coefficient;
    }

    public function edit(PricingGroupData $pricingGroupData)
    {
        $this->name = $pricingGroupData->name;
        $this->coefficient = $pricingGroupData->coefficient;
    }
}
