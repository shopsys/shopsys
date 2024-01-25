<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="availability_translations")
 * @ORM\Entity
 */
class AvailabilityTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\Availability")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translatable;

    /**
     * @ORM\Column(type="string", length=100)
     */
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
    public function setName($name)
    {
        $this->name = $name;
    }
}
