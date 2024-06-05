<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="parameter_groups")
 * @ORM\Entity
 * @method translation($locale = null): ParameterGroupTranslation
 * @method \Doctrine\Common\Collections\Collection<int, \App\Model\Product\Parameter\ParameterGroupTranslation> getTranslations()
 */
class ParameterGroup extends AbstractTranslatableEntity
{
    public const AKENEO_CODE_DIMENSIONS = 'param__dimensions';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Product\Parameter\ParameterGroupTranslation>
     * @Prezent\Translations(targetEntity="App\Model\Product\Parameter\ParameterGroupTranslation")
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $akeneoCode;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $orderingPriority;

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function __construct(ParameterGroupData $parameterGroupData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($parameterGroupData);

        $this->akeneoCode = $parameterGroupData->akeneoCode;
        $this->orderingPriority = $parameterGroupData->orderingPriority;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function edit(ParameterGroupData $parameterGroupData): void
    {
        $this->setTranslations($parameterGroupData);
        $this->orderingPriority = $parameterGroupData->orderingPriority;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterGroupTranslation
     */
    protected function createTranslation(): ParameterGroupTranslation
    {
        return new ParameterGroupTranslation();
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    private function setTranslations(ParameterGroupData $parameterGroupData): void
    {
        foreach ($parameterGroupData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @return string
     */
    public function getAkeneoCode(): string
    {
        return $this->akeneoCode;
    }

    /**
     * @return int
     */
    public function getOrderingPriority(): int
    {
        return $this->orderingPriority;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
