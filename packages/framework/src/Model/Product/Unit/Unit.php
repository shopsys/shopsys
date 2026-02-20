<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'units')]
#[ORM\Entity]
class Unit extends AbstractTranslatableEntity
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation>
     */
    #[Prezent\Translations(targetEntity: UnitTranslation::class)]
    #[Override]
    protected $translations;

    public function __construct(UnitData $unitData)
    {
        $this->translations = new ArrayCollection();
        $this->setData($unitData);
    }

    public function edit(UnitData $unitData): void
    {
        $this->setData($unitData);
    }

    protected function setData(UnitData $unitData): void
    {
        $this->setTranslations($unitData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    protected function setTranslations(UnitData $unitData): void
    {
        foreach ($unitData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\UnitTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new UnitTranslation();
    }
}
