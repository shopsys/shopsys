<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(
 *     name="categories",
 *     indexes={
 *         @ORM\Index(columns={"lft"}),
 *         @ORM\Index(columns={"rgt"}),
 *     }
 * )
 * @ORM\Entity
 */
class Category extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Category\CategoryTranslation>
     * @Prezent\Translations(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\CategoryTranslation")
     */
    protected $translations;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\Category", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, name="parent_id", referencedColumnName="id")
     */
    protected Category $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Category\Category>
     * @ORM\OneToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected Collection $children;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected int $level;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected int $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected int $rgt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Category\CategoryDomain>
     * @ORM\OneToMany(targetEntity="Tests\App\Functional\EntityExtension\Model\Category\CategoryDomain", mappedBy="category", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected Collection $domains;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->domains = new ArrayCollection();
    }

    public function setMandatoryData(): void
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->level = 1;
        $this->rgt = 1;
        $this->lft = 1;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\Category\CategoryTranslation
     */
    protected function createTranslation(): CategoryTranslation
    {
        return new CategoryTranslation();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
