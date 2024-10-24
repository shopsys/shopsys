<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="parameter_groups")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation translation(?string $locale = null)
 */
class ParameterGroup extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation")
     */
    protected $translations;

    /**
     * @var int
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function __construct(ParameterGroupData $parameterGroupData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($parameterGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function edit(ParameterGroupData $parameterGroupData): void
    {
        $this->setTranslations($parameterGroupData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation
     */
    protected function createTranslation()
    {
        return new ParameterGroupTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    protected function setTranslations(ParameterGroupData $parameterGroupData): void
    {
        foreach ($parameterGroupData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
