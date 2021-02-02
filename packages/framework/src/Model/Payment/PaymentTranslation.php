<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformString;

/**
 * @ORM\Table(name="payment_translations")
 * @ORM\Entity
 */
class PaymentTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     */
    protected $translatable;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instructions;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = TransformString::getTrimmedStringOrNullOnEmpty($name);
    }

    /**
     * @param string|null $description
     */
    public function setDescription($description)
    {
        $this->description = TransformString::getTrimmedStringOrNullOnEmpty($description);
    }

    /**
     * @param string|null $instructions
     */
    public function setInstructions($instructions)
    {
        $this->instructions = TransformString::getTrimmedStringOrNullOnEmpty($instructions);
    }
}
