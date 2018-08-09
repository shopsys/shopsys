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

    public function isVisible()
    {
        return $this->visible;
    }

    protected function setTranslations(ParameterData $parameterData)
    {
        foreach ($parameterData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation
     */
    protected function createTranslation()
    {
        return new ParameterTranslation();
    }

    public function edit(ParameterData $parameterData)
    {
        $this->setTranslations($parameterData);
        $this->visible = $parameterData->visible;
    }
}
