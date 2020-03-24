<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain as BaseCategoryDomain;

/**
 * @ORM\Table(
 *     name="category_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="category_domain", columns={"category_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 * @property \App\Model\Category\Category $category
 * @method __construct(\App\Model\Category\Category $category, int $domainId)
 */
class CategoryDomain extends BaseCategoryDomain
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $productSeriesListTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $productSeriesListDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $productSeriesListLink;

    /**
     * @return string|null
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param string|null $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string|null
     */
    public function getProductSeriesListTitle(): ?string
    {
        return $this->productSeriesListTitle;
    }

    /**
     * @param string|null $productSeriesListTitle
     */
    public function setProductSeriesListTitle(?string $productSeriesListTitle): void
    {
        $this->productSeriesListTitle = $productSeriesListTitle;
    }

    /**
     * @return string|null
     */
    public function getProductSeriesListDescription(): ?string
    {
        return $this->productSeriesListDescription;
    }

    /**
     * @param string|null $productSeriesListDescription
     */
    public function setProductSeriesListDescription(?string $productSeriesListDescription): void
    {
        $this->productSeriesListDescription = $productSeriesListDescription;
    }

    /**
     * @return string|null
     */
    public function getProductSeriesListLink(): ?string
    {
        return $this->productSeriesListLink;
    }

    /**
     * @param string|null $productSeriesListLink
     */
    public function setProductSeriesListLink(?string $productSeriesListLink): void
    {
        $this->productSeriesListLink = $productSeriesListLink;
    }
}
