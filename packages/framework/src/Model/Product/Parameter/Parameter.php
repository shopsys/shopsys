<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="parameters")
 * @ORM\Entity
 */
class Parameter extends AbstractTranslatableEntity
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation")
     */
    protected $translations;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    public function __construct(ParameterData $parameterData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($parameterData);
        $this->visible = $parameterData->visible;
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

    public function isVisible(): bool
    {
        return $this->visible;
    }

    protected function setTranslations(ParameterData $parameterData): void
    {
        foreach ($parameterData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation
    {
        return new ParameterTranslation();
    }

    public function edit(ParameterData $parameterData): void
    {
        $this->setTranslations($parameterData);
        $this->visible = $parameterData->visible;
    }
}
