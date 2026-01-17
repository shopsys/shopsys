<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

#[ORM\Table(name: 'blog_article_blog_category_domains')]
#[ORM\Index(columns: ['blog_category_id', 'domain_id'])]
#[ORM\Entity]
class BlogArticleBlogCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    #[ORM\JoinColumn(name: 'blog_article_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: BlogArticle::class, inversedBy: 'blogArticleBlogCategoryDomains')]
    protected $blogArticle;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    #[ORM\JoinColumn(name: 'blog_category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: BlogCategory::class)]
    protected $blogCategory;

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    public function __construct(BlogArticle $blogArticle, BlogCategory $blogCategory, int $domainId)
    {
        $this->blogArticle = $blogArticle;
        $this->blogCategory = $blogCategory;
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function getBlogCategory()
    {
        return $this->blogCategory;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }
}
