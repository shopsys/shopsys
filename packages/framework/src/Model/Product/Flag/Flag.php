<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="flags")
 * @ORM\Entity
 */
class Flag extends AbstractTranslatableEntity
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation")
     */
    protected $translations;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=7)
     */
    protected $rgbColor;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    public function __construct(FlagData $flagData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($flagData);
        $this->rgbColor = $flagData->rgbColor;
        $this->visible = $flagData->visible;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName(?string $locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    public function getRgbColor(): string
    {
        return $this->rgbColor;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    protected function setTranslations(FlagData $flagData): void
    {
        foreach ($flagData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation
    {
        return new FlagTranslation();
    }

    public function edit(FlagData $flagData): void
    {
        $this->setTranslations($flagData);
        $this->rgbColor = $flagData->rgbColor;
        $this->visible = $flagData->visible;
    }
}
