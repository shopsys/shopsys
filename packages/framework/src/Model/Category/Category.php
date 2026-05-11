<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterInterface;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation> getTranslations()
 */
#[ORM\Table(name: 'categories')]
#[ORM\Index(columns: ['lft'])]
#[ORM\Index(columns: ['rgt'])]
#[ORM\Entity]
#[Gedmo\Tree(type: 'nested')]
#[EntityImage]
class Category extends AbstractTranslatableEntity implements TreeSelectionEntityInterface
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
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation>
     */
    #[Prezent\Translations(targetEntity: CategoryTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    #[ORM\JoinColumn(nullable: true, name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[Gedmo\TreeParent]
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLevel]
    protected $level;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLeft]
    protected $lft;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeRight]
    protected $rgt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Category\CategoryDomain>
     */
    #[ORM\OneToMany(targetEntity: CategoryDomain::class, mappedBy: 'category', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    protected $automatedFilters;

    public function __construct(CategoryData $categoryData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->children = new ArrayCollection();

        $this->createDomains($categoryData);
        $this->uuid = $categoryData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($categoryData);
    }

    public function edit(CategoryData $categoryData): void
    {
        $this->setDomains($categoryData);
        $this->setData($categoryData);
    }

    protected function setData(CategoryData $categoryData): void
    {
        $this->setParent($categoryData->parent);
        $this->setTranslations($categoryData);
        $this->automatedFilters = $categoryData->automatedFilters;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $parent
     */
    public function setParent($parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    #[Override]
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getName(?string $locale = null)
    {
        return $this->translation($locale)->getName();
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    #[Override]
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Method does not lazy load children
     *
     * @return bool
     */
    #[Override]
    public function hasChildren()
    {
        return $this->getRgt() - $this->getLft() > 1;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    #[Override]
    public function getChildren()
    {
        return $this->children->getValues();
    }

    /**
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryDomain
     */
    protected function getCategoryDomain($domainId)
    {
        foreach ($this->domains as $categoryDomain) {
            if ($categoryDomain->getDomainId() === $domainId) {
                return $categoryDomain;
            }
        }

        throw new CategoryDomainNotFoundException($domainId, $this->id);
    }

    protected function setTranslations(CategoryData $categoryData): void
    {
        foreach ($categoryData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->getSeoTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoH1(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->getSeoH1();
    }

    /**
     * @return bool
     */
    public function isEnabled(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->isEnabled();
    }

    /**
     * @return bool
     */
    #[Override]
    public function isVisible(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->isVisible();
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @return string|null
     */
    public function getDescription(int $domainId)
    {
        return $this->getCategoryDomain($domainId)->getDescription();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string[]
     */
    public function getAutomatedFilters()
    {
        return $this->automatedFilters;
    }

    public function isUsingAutomatedFilter(CategoryAutomatedFilterInterface $automatedFilter): bool
    {
        return in_array($automatedFilter->getDatabaseValue(), $this->automatedFilters, true);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new CategoryTranslation();
    }

    protected function setDomains(CategoryData $categoryData): void
    {
        foreach ($this->domains as $categoryDomain) {
            $domainId = $categoryDomain->getDomainId();
            $categoryDomain->setSeoTitle($categoryData->seoTitles[$domainId]);
            $categoryDomain->setSeoH1($categoryData->seoH1s[$domainId]);
            $categoryDomain->setSeoMetaDescription($categoryData->seoMetaDescriptions[$domainId]);
            $categoryDomain->setDescription($categoryData->descriptions[$domainId]);
            $categoryDomain->setEnabled($categoryData->enabled[$domainId]);
        }
    }

    protected function createDomains(CategoryData $categoryData): void
    {
        $domainIds = array_keys($categoryData->seoTitles);

        foreach ($domainIds as $domainId) {
            $categoryDomain = new CategoryDomain($this, $domainId);
            $this->domains->add($categoryDomain);
        }

        $this->setDomains($categoryData);
    }
}
