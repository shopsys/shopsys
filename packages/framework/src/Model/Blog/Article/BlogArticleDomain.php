<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'blog_article_domains')]
#[ORM\Entity]
class BlogArticleDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'blog_article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: BlogArticle::class, inversedBy: 'domains')]
    protected $blogArticle;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    #[AsMcpColumn]
    #[ORM\Embedded(class: SeoAttributes::class)]
    protected $seo;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $visible;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 25)]
    protected $status;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $publishDate;

    public function __construct(BlogArticle $blogArticle, int $domainId)
    {
        $this->blogArticle = $blogArticle;
        $this->domainId = $domainId;
        $this->visible = false;
        $this->status = BlogArticleStatusEnum::STATUS_DRAFT;
        $this->publishDate = null;
        $this->seo = new SeoAttributes();
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
     * @return \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    public function getSeoAttributes()
    {
        return $this->seo;
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
