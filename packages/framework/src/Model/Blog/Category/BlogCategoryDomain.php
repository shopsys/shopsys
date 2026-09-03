<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'blog_category_domains')]
#[ORM\Entity]
class BlogCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'blog_category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: BlogCategory::class, inversedBy: 'domains')]
    protected $blogCategory;

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
    protected $enabled;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $visible;

    public function __construct(BlogCategory $blogCategory, int $domainId)
    {
        $this->blogCategory = $blogCategory;
        $this->domainId = $domainId;
        $this->enabled = true;
        $this->visible = false;
        $this->seo = new SeoAttributes();
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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }
}
