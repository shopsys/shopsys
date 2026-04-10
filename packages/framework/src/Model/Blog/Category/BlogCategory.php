<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method translation($locale = null): BlogCategoryTranslation
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'blog_categories')]
#[ORM\Entity]
#[Gedmo\Tree(type: 'nested')]
#[EntityImage]
class BlogCategory extends AbstractTranslatableEntity implements TreeSelectionEntityInterface
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryTranslation>
     */
    #[Prezent\Translations(targetEntity: BlogCategoryTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[Gedmo\TreeParent]
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLevel]
    protected $level;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeLeft]
    protected $lft;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\TreeRight]
    protected $rgt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDomain>
     */
    #[ORM\OneToMany(targetEntity: BlogCategoryDomain::class, mappedBy: 'blogCategory', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    public function __construct(BlogCategoryData $blogCategoryData)
    {
        $this->setParent($blogCategoryData->parent);
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->children = new ArrayCollection();

        $this->setTranslations($blogCategoryData);
        $this->uuid = $blogCategoryData->uuid ?: Uuid::uuid4()->toString();
    }

    public function edit(BlogCategoryData $blogCategoryData): void
    {
        $this->setParent($blogCategoryData->parent);
        $this->setTranslations($blogCategoryData);
        $this->setDomains($blogCategoryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null $parent
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
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null
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
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
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

    protected function getDomain(int $domainId): BlogCategoryDomain
    {
        foreach ($this->domains as $blogCategoryDomain) {
            if ($blogCategoryDomain->getDomainId() === $domainId) {
                return $blogCategoryDomain;
            }
        }

        throw new BlogCategoryDomainNotFoundException($this->id, $domainId);
    }

    protected function setTranslations(BlogCategoryData $blogCategoryData): void
    {
        foreach ($blogCategoryData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($blogCategoryData->descriptions as $locale => $name) {
            $this->translation($locale)->setDescription($name);
        }
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getDomain($domainId)->getSeoTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoH1(int $domainId)
    {
        return $this->getDomain($domainId)->getSeoH1();
    }

    /**
     * @return bool
     */
    public function isEnabled(int $domainId)
    {
        return $this->getDomain($domainId)->isEnabled();
    }

    /**
     * @return bool
     */
    #[Override]
    public function isVisible(int $domainId)
    {
        return $this->getDomain($domainId)->isVisible();
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @return string|null
     */
    public function getDescription(?string $locale = null)
    {
        return $this->translation($locale)->getDescription();
    }

    /**
     * @return string[]
     */
    public function getDescriptions()
    {
        $descriptionsByLocale = [];

        foreach ($this->translations as $translation) {
            $descriptionsByLocale[$translation->getLocale()] = $translation->getDescription();
        }

        return $descriptionsByLocale;
    }

    #[Override]
    protected function createTranslation(): BlogCategoryTranslation
    {
        return new BlogCategoryTranslation();
    }

    protected function setDomains(BlogCategoryData $blogCategoryData): void
    {
        foreach ($this->domains as $blogCategoryDomain) {
            $domainId = $blogCategoryDomain->getDomainId();
            $blogCategoryDomain->setSeoTitle($blogCategoryData->seoTitles[$domainId]);
            $blogCategoryDomain->setSeoH1($blogCategoryData->seoH1s[$domainId]);
            $blogCategoryDomain->setSeoMetaDescription($blogCategoryData->seoMetaDescriptions[$domainId]);
            $blogCategoryDomain->setEnabled($blogCategoryData->enabled[$domainId]);
        }
    }

    public function createDomains(BlogCategoryData $blogCategoryData): void
    {
        $domainIds = array_keys($blogCategoryData->seoTitles);

        foreach ($domainIds as $domainId) {
            $blogCategoryDomain = new BlogCategoryDomain($this, $domainId);
            $this->domains[] = $blogCategoryDomain;
        }

        $this->setDomains($blogCategoryData);
    }

    /**
     * @return bool
     */
    public function isMainPage()
    {
        return $this->parent === null;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
