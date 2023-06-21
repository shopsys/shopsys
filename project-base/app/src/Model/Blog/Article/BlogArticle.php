<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use App\Model\Blog\Article\Exception\BlogArticleDomainNotFoundException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="blog_articles")
 * @ORM\Entity
 * @method translation($locale = null): BlogArticleTranslation
 */
class BlogArticle extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\App\Model\Blog\Article\BlogArticleBlogCategoryDomain[]
     * @ORM\OneToMany(
     *   targetEntity="App\Model\Blog\Article\BlogArticleBlogCategoryDomain",
     *   mappedBy="blogArticle",
     *   orphanRemoval=true,
     *   cascade={"persist"}
     * )
     */
    private $blogArticleBlogCategoryDomains;

    /**
     * @var \App\Model\Blog\Article\BlogArticleTranslation[]|\Doctrine\Common\Collections\ArrayCollection
     * @Prezent\Translations(targetEntity="App\Model\Blog\Article\BlogArticleTranslation")
     */
    protected $translations;

    /**
     * @var \App\Model\Blog\Article\BlogArticleDomain[]|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Blog\Article\BlogArticleDomain", mappedBy="blogArticle", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $domains;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $hidden;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $visibleOnHomepage;

    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    private $publishDate;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    private string $uuid;

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     */
    public function __construct(BlogArticleData $blogArticleData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->blogArticleBlogCategoryDomains = new ArrayCollection();

        $this->setTranslations($blogArticleData);

        $this->hidden = $blogArticleData->hidden;
        $this->createdAt = new DateTime();
        $this->visibleOnHomepage = $blogArticleData->visibleOnHomepage;
        $this->publishDate = $blogArticleData->publishDate ?? new DateTime();
        $this->uuid = $blogArticleData->uuid ?: Uuid::uuid4()->toString();
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     * @param \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory
     */
    public function edit(
        BlogArticleData $blogArticleData,
        BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory,
    ): void {
        $this->setTranslations($blogArticleData);
        $this->setDomains($blogArticleData);
        $this->setCategories($blogArticleBlogCategoryDomainFactory, $blogArticleData->blogCategoriesByDomainId);

        $this->hidden = $blogArticleData->hidden;
        $this->visibleOnHomepage = $blogArticleData->visibleOnHomepage;
        $this->publishDate = $blogArticleData->publishDate ?? new DateTime();
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
     * @return string
     */
    public function getName(?string $locale = null): ?string
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
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticleDomain
     */
    private function getDomain(int $domainId): BlogArticleDomain
    {
        foreach ($this->domains as $blogArticleDomain) {
            if ($blogArticleDomain->getDomainId() === $domainId) {
                return $blogArticleDomain;
            }
        }

        throw new BlogArticleDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory
     * @param \App\Model\Blog\Category\BlogCategory[][] $blogCategoriesByDomainId
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
     * @param \App\Model\Blog\Article\BlogArticleBlogCategoryDomainFactory $blogArticleBlogCategoryDomainFactory
     * @param \App\Model\Blog\Category\BlogCategory[] $newBlogCategories
     * @param int $domainId
     */
    private function createNewBlogArticleBlogCategoryDomains(
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
     * @param \App\Model\Blog\Category\BlogCategory[] $newBlogCategories
     * @param int $domainId
     */
    private function removeOldBlogArticleBlogCategoryDomains(array $newBlogCategories, int $domainId): void
    {
        $currentBlogArticleBlogCategoryDomains = $this->getBlogArticleBlogCategoryDomainsByDomainIdIndexedByCategoryId($domainId);

        foreach ($currentBlogArticleBlogCategoryDomains as $currentBlogArticleBlogCategoryDomain) {
            if (!in_array($currentBlogArticleBlogCategoryDomain->getBlogCategory(), $newBlogCategories, true)) {
                $this->blogArticleBlogCategoryDomains->removeElement($currentBlogArticleBlogCategoryDomain);
            }
        }
    }

    /**
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticleBlogCategoryDomain[]
     */
    private function getBlogArticleBlogCategoryDomainsByDomainIdIndexedByCategoryId(int $domainId): array
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
     * @return \App\Model\Blog\Category\BlogCategory[][]
     */
    public function getBlogCategoriesIndexedByDomainId()
    {
        $blogCategoriesByDomainId = [];

        foreach ($this->blogArticleBlogCategoryDomains as $blogArticleBlogCategoryDomain) {
            $blogCategoriesByDomainId[$blogArticleBlogCategoryDomain->getDomainId()][] = $blogArticleBlogCategoryDomain->getBlogCategory();
        }

        return $blogCategoriesByDomainId;
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     */
    private function setTranslations(BlogArticleData $blogArticleData): void
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
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId): ?string
    {
        return $this->getDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoH1(int $domainId): ?string
    {
        return $this->getDomain($domainId)->getSeoH1();
    }

    /**;
     *
     * @param $domainId
     * @return bool
     */

    /**
     * @param int $domainId
     * @return bool
     */
    public function isVisible(int $domainId): bool
    {
        return $this->getDomain($domainId)->isVisible();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId): ?string
    {
        return $this->getDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getDescription(?string $locale = null): ?string
    {
        return $this->translation($locale)->getDescription();
    }

    /**
     * @return string[]
     */
    public function getDescriptions(): array
    {
        $descriptionsByLocale = [];

        foreach ($this->translations as $translation) {
            $descriptionsByLocale[$translation->getLocale()] = $translation->getDescription();
        }

        return $descriptionsByLocale;
    }

    /**
     * @return \App\Model\Blog\Article\BlogArticleTranslation
     */
    protected function createTranslation(): BlogArticleTranslation
    {
        return new BlogArticleTranslation();
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     */
    private function setDomains(BlogArticleData $blogArticleData): void
    {
        foreach ($this->domains as $blogArticleDomain) {
            $domainId = $blogArticleDomain->getDomainId();
            $blogArticleDomain->setSeoTitle($blogArticleData->seoTitles[$domainId]);
            $blogArticleDomain->setSeoH1($blogArticleData->seoH1s[$domainId]);
            $blogArticleDomain->setSeoMetaDescription($blogArticleData->seoMetaDescriptions[$domainId]);
        }
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticleData $blogArticleData
     */
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
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isVisibleOnHomepage(): bool
    {
        return $this->visibleOnHomepage;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDate(): DateTime
    {
        return $this->publishDate;
    }

    /**
     * @return string[]
     */
    public function getPerexes(): array
    {
        $perexesByLocale = [];

        foreach ($this->translations as $translation) {
            $perexesByLocale[$translation->getLocale()] = $translation->getPerex();
        }

        return $perexesByLocale;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getPerex(?string $locale = null): ?string
    {
        return $this->translation($locale)->getPerex();
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
