<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="category_translations")
 * @ORM\Entity
 */
class CategoryTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category")
     */
    protected $translatable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

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
