<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category;

use App\Model\Product\Series\Category\Exception\ProductSeriesCategoryDomainNotFoundException;
use App\Model\Product\Series\ProductSeriesTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * Class ProductSeriesCategory
 *
 * @ORM\Table(name="product_series_categories")
 * @ORM\Entity
 *
 * @method ProductSeriesTranslation translation(?string $locale = null)
 */
class ProductSeriesCategory extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryTranslation[]|\Doctrine\Common\Collections\Collection
     *
     * @Prezent\Translations(targetEntity="App\Model\Product\Series\Category\ProductSeriesCategoryTranslation")
     */
    protected $translations;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryDomain[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="App\Model\Product\Series\Category\ProductSeriesCategoryDomain", mappedBy="productSeriesCategory", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     */
    public function __construct(ProductSeriesCategoryData $productSeriesCategoryData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->setTranslations($productSeriesCategoryData);
        $this->createDomains($productSeriesCategoryData);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     */
    public function edit(ProductSeriesCategoryData $productSeriesCategoryData): void
    {
        $this->setTranslations($productSeriesCategoryData);
        $this->setDomains($productSeriesCategoryData);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     */
    private function setTranslations(ProductSeriesCategoryData $productSeriesCategoryData): void
    {
        foreach ($productSeriesCategoryData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($productSeriesCategoryData->description as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     */
    private function createDomains(ProductSeriesCategoryData $productSeriesCategoryData): void
    {
        $domainIds = array_keys($productSeriesCategoryData->seoTitle);

        foreach ($domainIds as $domainId) {
            $productSeriesCategoryDomain = new ProductSeriesCategoryDomain($this, $domainId);
            $this->domains->add($productSeriesCategoryDomain);
        }

        $this->setDomains($productSeriesCategoryData);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryData $productSeriesCategoryData
     */
    private function setDomains(ProductSeriesCategoryData $productSeriesCategoryData): void
    {
        foreach ($this->domains as $productSeriesCategoryDomain) {
            $domainId = $productSeriesCategoryDomain->getDomainId();
            $productSeriesCategoryDomain->setSeoTitle($productSeriesCategoryData->seoTitle[$domainId]);
            $productSeriesCategoryDomain->setSeoH1($productSeriesCategoryData->seoH1[$domainId]);
            $productSeriesCategoryDomain->setSeoMetaDescription($productSeriesCategoryData->seoMetaDescription[$domainId]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function createTranslation()
    {
        return new ProductSeriesCategoryTranslation();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Series\Category\ProductSeriesCategoryDomain
     */
    private function getProductSeriesCategoryDomain(int $domainId): ProductSeriesCategoryDomain
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        throw new ProductSeriesCategoryDomainNotFoundException($this->getId(), $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getProductSeriesCategoryDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getProductSeriesCategoryDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoH1(int $domainId)
    {
        return $this->getProductSeriesCategoryDomain($domainId)->getSeoH1();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName(?string $locale = null): ?string
    {
        return $this->translation($locale)->getName();
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
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }
}
