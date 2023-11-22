<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryDomainNotFoundException;
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
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation translation(?string $locale = null)
 */
class Category extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation[]|\Doctrine\Common\Collections\Collection
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Category\CategoryTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", inversedBy="children")
     * @ORM\JoinColumn(nullable=true, name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @var int
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $level;

    /**
     * @var int
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @var int
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryDomain[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Category\CategoryDomain", mappedBy="category", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    public function __construct(CategoryData $categoryData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->children = new ArrayCollection();

        $this->createDomains($categoryData);
        $this->uuid = $categoryData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($categoryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    public function edit(CategoryData $categoryData): void
    {
        $this->setDomains($categoryData);
        $this->setData($categoryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    protected function setData(CategoryData $categoryData): void
    {
        $this->setParent($categoryData->parent);
        $this->setTranslations($categoryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $parent
     */
    public function setParent(?self $parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function getParent(): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Method does not lazy load children
     *
     * @return bool
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
        return $this->children->getValues();
    }

    /**
     * @return int
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @return int
     */
    public function getRgt(): int
    {
        return $this->rgt;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryDomain
     */
    protected function getCategoryDomain($domainId): \Shopsys\FrameworkBundle\Model\Category\CategoryDomain
    {
        foreach ($this->domains as $categoryDomain) {
            if ($categoryDomain->getDomainId() === $domainId) {
                return $categoryDomain;
            }
        }

        throw new CategoryDomainNotFoundException($domainId, $this->id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
    protected function setTranslations(CategoryData $categoryData): void
    {
        foreach ($categoryData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoH1(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoH1();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isEnabled(int $domainId): bool
    {
        return $this->getCategoryDomain($domainId)->isEnabled();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isVisible(int $domainId): bool
    {
        return $this->getCategoryDomain($domainId)->isVisible();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getDescription(int $domainId): ?string
    {
        return $this->getCategoryDomain($domainId)->getDescription();
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation
     */
    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Category\CategoryTranslation
    {
        return new CategoryTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     */
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
