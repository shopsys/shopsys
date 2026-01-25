<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

#[ORM\Table(name: 'flag_translations')]
#[ORM\Entity]
class FlagTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    #[Prezent\Translatable(targetEntity: Flag::class)]
    protected $translatable;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }
}
