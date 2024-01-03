<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

/**
 * @ORM\Table(
 *     name="blog_article_blog_category_domains",
 *     indexes={@ORM\Index(columns={"blog_category_id", "domain_id"})}
 * )
 * @ORM\Entity
 */
class BlogArticleBlogCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle", inversedBy="blogArticleBlogCategoryDomains")
     * @ORM\JoinColumn(name="blog_article_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $blogArticle;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory")
     * @ORM\JoinColumn(name="blog_category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $blogCategory;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle $blogArticle
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     */
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
