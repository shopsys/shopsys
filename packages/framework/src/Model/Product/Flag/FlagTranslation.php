<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="flag_translations")
 * @ORM\Entity
 */
class FlagTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
