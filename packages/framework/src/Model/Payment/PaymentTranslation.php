<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instructions;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    
    public function setInstructions(string $instructions): void
    {
        $this->instructions = $instructions;
    }
}
