<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="parameter_units")
 * @ORM\Entity
 *
 * @method \App\Model\Product\Parameter\Unit\ParameterUnitTranslation translation(?string $locale = null)
 */
class ParameterUnit extends AbstractTranslatableEntity
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
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    protected $unit;

    /**
     * @var \App\Model\Product\Parameter\Unit\ParameterUnitTranslation[]|\Doctrine\Common\Collections\Collection
     *
     * @Prezent\Translations(targetEntity="App\Model\Product\Parameter\Unit\ParameterUnitTranslation")
     */
    protected $translations;

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnitData
     */
    public function __construct(ParameterUnitData $parameterUnitData)
    {
        $this->unit = $parameterUnitData->unit;
        $this->translations = new ArrayCollection();
        $this->setTranslations($parameterUnitData);
    }

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnitData
     */
    public function edit(ParameterUnitData $parameterUnitData): void
    {
        $this->setTranslations($parameterUnitData);
    }

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnitData $parameterUnitData
     */
    private function setTranslations(ParameterUnitData $parameterUnitData): void
    {
        foreach ($parameterUnitData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName() ?? $this->getUnit();
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @return \App\Model\Product\Parameter\Unit\ParameterUnitTranslation
     */
    protected function createTranslation(): ParameterUnitTranslation
    {
        return new ParameterUnitTranslation();
    }
}
