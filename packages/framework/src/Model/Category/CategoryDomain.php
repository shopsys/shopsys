<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="category_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_domain", columns={"category_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class CategoryDomain
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Category\Category", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoH1;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
    public function __construct(Category $category, int $domainId)
    {
        $this->category = $category;
        $this->domainId = $domainId;
        $this->enabled = true;
        $this->visible = false;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    public function setSeoMetaDescription(?string $seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    public function setSeoH1(?string $seoH1): void
    {
        $this->seoH1 = $seoH1;
    }
    
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
    
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }
}
