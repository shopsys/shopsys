<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="units")
 * @ORM\Entity
 */
class Unit extends AbstractTranslatableEntity
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation")
     */
    protected $translations;

    public function __construct(UnitData $unitData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($unitData);
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    protected function setTranslations(UnitData $unitData)
    {
        foreach ($unitData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation
    {
        return new UnitTranslation();
    }

    public function edit(UnitData $unitData)
    {
        $this->setTranslations($unitData);
    }
}
