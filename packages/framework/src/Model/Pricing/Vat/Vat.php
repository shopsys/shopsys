<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="vats")
 * @ORM\Entity
 */
class Vat
{
    const SETTING_DEFAULT_VAT = 'defaultVatId';

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
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    protected $percent;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $replaceWith;

    public function __construct(VatData $vatData)
    {
        $this->name = $vatData->name;
        $this->percent = $vatData->percent;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

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

    public function edit(VatData $vatData)
    {
        $this->name = $vatData->name;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    public function markForDeletion(self $newVat)
    {
        $this->replaceWith = $newVat;
    }

    public function isMarkedAsDeleted()
    {
        return $this->replaceWith !== null;
    }
}
