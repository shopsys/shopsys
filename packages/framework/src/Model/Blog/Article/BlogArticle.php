<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method translation($locale = null): BlogArticleTranslation
 */
#[ORM\Table(name: 'blog_articles')]
#[ORM\Entity]
class BlogArticle extends AbstractTranslatableEntity
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleBlogCategoryDomain>
     */
    #[ORM\OneToMany(targetEntity: BlogArticleBlogCategoryDomain::class, mappedBy: 'blogArticle', orphanRemoval: true, cascade: ['persist'])]
    protected $blogArticleBlogCategoryDomains;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleTranslation>
     */
    #[Prezent\Translations(targetEntity: BlogArticleTranslation::class)]
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain>
     */
    #[ORM\OneToMany(targetEntity: BlogArticleDomain::class, mappedBy: 'blogArticle', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $visibleOnHomepage;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $publishDate;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    public function __construct(BlogArticleData $blogArticleData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->blogArticleBlogCategoryDomains = new ArrayCollection();

        $this->setTranslations($blogArticleData);

        $this->hidden = $blogArticleData->hidden;
        $this->createdAt = $blogArticleData->createdAt;
        $this->visibleOnHomepage = $blogArticleData->visibleOnHomepage;
        $this->publishDate = $blogArticleData->publishDate;
        $this->uuid = $blogArticleData->uuid ?: Uuid::uuid4()->toString();
    }

    public function edit(
        BlogArticleData $blogArticleData,
        BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
    ): void {
        $this->setTranslations($blogArticleData);
        $this->setDomains($blogArticleData);
        $this->setCategories($blogArticleBlogCategoryDomainFactory, $blogArticleData->blogCategoriesByDomainId);

        $this->hidden = $blogArticleData->hidden;
        $this->visibleOnHomepage = $blogArticleData->visibleOnHomepage;
        $this->publishDate = $blogArticleData->publishDate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
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

    protected function getDomain(int $domainId): BlogArticleDomain
    {
        foreach ($this->domains as $blogArticleDomain) {
            if ($blogArticleDomain->getDomainId() === $domainId) {
                return $blogArticleDomain;
            }
        }

        throw new BlogArticleDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][] $blogCategoriesByDomainId
     */
    public function setCategories(
        BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
        array $blogCategoriesByDomainId,
    ): void {
        foreach ($blogCategoriesByDomainId as $domainId => $blogCategories) {
            $this->removeOldBlogArticleBlogCategoryDomains($blogCategories, $domainId);
            $this->createNewBlogArticleBlogCategoryDomains($blogArticleBlogCategoryDomainFactory, $blogCategories, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $newBlogCategories
     */
    protected function createNewBlogArticleBlogCategoryDomains(
        BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
        array $newBlogCategories,
        int $domainId,
    ): void {
        $currentBlogArticleBlogCategoryDomainsOnDomainByCategoryId = $this->getBlogArticleBlogCategoryDomainsByDomainIdIndexedByCategoryId($domainId);

        foreach ($newBlogCategories as $newBlogCategory) {
            if (!array_key_exists($newBlogCategory->getId(), $currentBlogArticleBlogCategoryDomainsOnDomainByCategoryId)) {
                $blogArticleBlogCategoryDomain = $blogArticleBlogCategoryDomainFactory->create($this, $newBlogCategory, $domainId);
                $this->blogArticleBlogCategoryDomains->add($blogArticleBlogCategoryDomain);
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $newBlogCategories
     */
    protected function removeOldBlogArticleBlogCategoryDomains(array $newBlogCategories, int $domainId): void
    {
        $currentBlogArticleBlogCategoryDomains = $this->getBlogArticleBlogCategoryDomainsByDomainIdIndexedByCategoryId($domainId);

        foreach ($currentBlogArticleBlogCategoryDomains as $currentBlogArticleBlogCategoryDomain) {
            if (!in_array($currentBlogArticleBlogCategoryDomain->getBlogCategory(), $newBlogCategories, true)) {
                $this->blogArticleBlogCategoryDomains->removeElement($currentBlogArticleBlogCategoryDomain);
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleBlogCategoryDomain[]
     */
    protected function getBlogArticleBlogCategoryDomainsByDomainIdIndexedByCategoryId(int $domainId): array
    {
        $blogArticleBlogCategoryDomainsByCategoryId = [];

        foreach ($this->blogArticleBlogCategoryDomains as $blogArticleBlogCategoryDomain) {
            if ($blogArticleBlogCategoryDomain->getDomainId() === $domainId) {
                $blogArticleBlogCategoryDomainsByCategoryId[$blogArticleBlogCategoryDomain->getBlogCategory()->getId()] = $blogArticleBlogCategoryDomain;
            }
        }

        return $blogArticleBlogCategoryDomainsByCategoryId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][]
     */
    public function getBlogCategoriesIndexedByDomainId()
    {
        $blogCategoriesByDomainId = [];

        foreach ($this->blogArticleBlogCategoryDomains as $blogArticleBlogCategoryDomain) {
            $blogCategoriesByDomainId[$blogArticleBlogCategoryDomain->getDomainId()][] = $blogArticleBlogCategoryDomain->getBlogCategory();
        }

        return $blogCategoriesByDomainId;
    }

    protected function setTranslations(BlogArticleData $blogArticleData): void
    {
        foreach ($blogArticleData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($blogArticleData->descriptions as $locale => $name) {
            $this->translation($locale)->setDescription($name);
        }

        foreach ($blogArticleData->perexes as $locale => $name) {
            $this->translation($locale)->setPerex($name);
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
    protected function createTranslation(): BlogArticleTranslation
    {
        return new BlogArticleTranslation();
    }

    protected function setDomains(BlogArticleData $blogArticleData): void
    {
        foreach ($this->domains as $blogArticleDomain) {
            $domainId = $blogArticleDomain->getDomainId();
            $blogArticleDomain->setSeoTitle($blogArticleData->seoTitles[$domainId]);
            $blogArticleDomain->setSeoH1($blogArticleData->seoH1s[$domainId]);
            $blogArticleDomain->setSeoMetaDescription($blogArticleData->seoMetaDescriptions[$domainId]);
        }
    }

    public function createDomains(BlogArticleData $blogArticleData): void
    {
        $domainIds = array_keys($blogArticleData->seoTitles);

        foreach ($domainIds as $domainId) {
            $categoryDomain = new BlogArticleDomain($this, $domainId);
            $this->domains[] = $categoryDomain;
        }

        $this->setDomains($blogArticleData);
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isVisibleOnHomepage()
    {
        return $this->visibleOnHomepage;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @return string[]
     */
    public function getPerexes()
    {
        $perexesByLocale = [];

        foreach ($this->translations as $translation) {
            $perexesByLocale[$translation->getLocale()] = $translation->getPerex();
        }

        return $perexesByLocale;
    }

    /**
     * @return string|null
     */
    public function getPerex(?string $locale = null)
    {
        return $this->translation($locale)->getPerex();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
