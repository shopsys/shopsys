<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="categories")
 * @ORM\Entity
 */
class Category extends AbstractTranslatableEntity
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
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Category\CategoryTranslation")
     */
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @var int
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $level;

    /**
     * @var int
     *
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @var int
     *
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryDomain[]|\Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\CategoryDomain", mappedBy="category", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    public function __construct(CategoryData $categoryData)
    {
        $this->setParent($categoryData->parent);
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();

        $this->setTranslations($categoryData);
        $this->createDomains($categoryData);
    }

    public function edit(CategoryData $categoryData)
    {
        $this->setParent($categoryData->parent);
        $this->setTranslations($categoryData);
        $this->setDomains($categoryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $parent
     */
    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
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

    public function getParent(): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Method does not lazy load children
     */
    public function hasChildren(): bool
    {
        return $this->getRgt() - $this->getLft() > 1;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param int $domainId
     */
    protected function getCategoryDomain($domainId): \Shopsys\FrameworkBundle\Model\Category\CategoryDomain
    {
        foreach ($this->domains as $categoryDomain) {
            if ($categoryDomain->getDomainId() === $domainId) {
                return $categoryDomain;
            }
        }

        throw new CategoryDomainNotFoundException($this->id, $domainId);
    }

    protected function setTranslations(CategoryData $categoryData)
    {
        foreach ($categoryData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    public function getSeoTitle(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoTitle();
    }

    public function getSeoH1(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoH1();
    }

    public function isEnabled(int $domainId): bool
    {
        return $this->getCategoryDomain($domainId)->isEnabled();
    }

    public function isVisible(int $domainId): bool
    {
        return $this->getCategoryDomain($domainId)->isVisible();
    }

    public function getSeoMetaDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoMetaDescription();
    }

    public function getDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getDescription();
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation
    {
        return new CategoryTranslation();
    }

    protected function setDomains(CategoryData $categoryData)
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

    protected function createDomains(CategoryData $categoryData)
    {
        $domainIds = array_keys($categoryData->seoTitles);

        foreach ($domainIds as $domainId) {
            $categoryDomain = new CategoryDomain($this, $domainId);
            $this->domains[] = $categoryDomain;
        }

        $this->setDomains($categoryData);
    }
}
