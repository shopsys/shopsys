<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'blog_article_domains')]
#[ORM\Entity]
class BlogArticleDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    #[ORM\JoinColumn(nullable: false, name: 'blog_article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: BlogArticle::class, inversedBy: 'domains')]
    protected $blogArticle;

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $seoTitle;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $seoMetaDescription;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $seoH1;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $visible;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 25)]
    protected $status;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $publishDate;

    public function __construct(BlogArticle $blogArticle, int $domainId)
    {
        $this->blogArticle = $blogArticle;
        $this->domainId = $domainId;
        $this->visible = false;
        $this->status = BlogArticleStatusEnum::STATUS_DRAFT;
        $this->publishDate = null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    public function getBlogArticle()
    {
        return $this->blogArticle;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1()
    {
        return $this->seoH1;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle($seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription($seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @param string|null $seoH1
     */
    public function setSeoH1($seoH1): void
    {
        $this->seoH1 = $seoH1;
    }

    /**
     * @param string $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @param \DateTimeImmutable|null $publishDate
     */
    public function setPublishDate($publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    public function isAccessibleOnStorefront(): bool
    {
        if ($this->status === BlogArticleStatusEnum::STATUS_PREVIEW) {
            return true;
        }

        return $this->status === BlogArticleStatusEnum::STATUS_PUBLISHED
            && $this->visible
            && ($this->publishDate === null || $this->publishDate <= new DateTimeImmutable());
    }
}
