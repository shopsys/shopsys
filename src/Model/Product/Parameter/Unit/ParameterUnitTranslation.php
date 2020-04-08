<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="parameter_unit_translations")
 * @ORM\Entity
 */
class ParameterUnitTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="App\Model\Product\Parameter\Unit\ParameterUnit")
     */
    protected $translatable;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
