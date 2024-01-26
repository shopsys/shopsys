<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="blog_category_domains"
 * )
 * @ORM\Entity
 */
class BlogCategoryDomain
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="blog_category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $blogCategory;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     */
    public function __construct(BlogCategory $blogCategory, int $domainId)
    {
        $this->blogCategory = $blogCategory;
        $this->domainId = $domainId;
        $this->enabled = true;
        $this->visible = false;
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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
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
     * @param bool $enabled
     */
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
