<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

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
     * @var \App\Model\Blog\Category\BlogCategory
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Model\Blog\Category\BlogCategory", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="blog_category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $blogCategory;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $seoH1;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $visible;

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
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
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription(?string $seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @param string|null $seoH1
     */
    public function setSeoH1(?string $seoH1): void
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
    public function isVisible(): bool
    {
        return $this->visible;
    }
}
