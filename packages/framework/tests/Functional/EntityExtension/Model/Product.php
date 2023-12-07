<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Functional\EntityExtension\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 */
class Product extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected string $catnum;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @Prezent\Translations(targetEntity="Tests\FrameworkBundle\Functional\EntityExtension\Model\ProductTranslation")
     */
    protected $translations;

    /**
     * @param string $catnum
     */
    public function __construct(
        string $catnum,
    ) {
        $this->catnum = $catnum;
        $this->translations = new ArrayCollection();
    }

    /**
     * @return \Tests\FrameworkBundle\Functional\EntityExtension\Model\ProductTranslation
     */
    protected function createTranslation()
    {
        return new ProductTranslation();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCatnum(): string
    {
        return $this->catnum;
    }
}
