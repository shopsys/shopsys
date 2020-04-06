<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use App\Model\Product\Type\Exception\ProductTypeDomainNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="product_types")
 * @ORM\Entity
 *
 * @method \App\Model\Product\Type\ProductTypeTranslation[] getTranslations()
 * @method \App\Model\Product\Type\ProductTypeTranslation translation(?string $locale)
 */
class ProductType extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    private const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \App\Model\Product\Type\ProductTypeTranslation[]|\Doctrine\Common\Collections\Collection
     *
     * @Prezent\Translations(targetEntity="App\Model\Product\Type\ProductTypeTranslation")
     */
    protected $translations;

    /**
     * @var \App\Model\Product\Type\ProductTypeDomain[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="App\Model\Product\Type\ProductTypeDomain", mappedBy="productType", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, unique=true)
     */
    protected $akeneoCode;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    public function __construct(ProductTypeData $productTypeData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->setTranslations($productTypeData);
        $this->createDomains($productTypeData);
        $this->akeneoCode = $productTypeData->akeneoCode;
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    public function edit(ProductTypeData $productTypeData): void
    {
        $this->setTranslations($productTypeData);
        $this->setDomains($productTypeData);
        $this->akeneoCode = $productTypeData->akeneoCode;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    protected function setTranslations(ProductTypeData $productTypeData): void
    {
        foreach ($productTypeData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \App\Model\Product\Type\ProductTypeTranslation
     */
    protected function createTranslation(): ProductTypeTranslation
    {
        return new ProductTypeTranslation();
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    private function createDomains(ProductTypeData $productTypeData): void
    {
        $domainIds = array_keys($productTypeData->freeTransport);

        foreach ($domainIds as $domainId) {
            $productTypeDomain = new ProductTypeDomain($this, $domainId);
            $this->domains->add($productTypeDomain);
        }

        $this->setDomains($productTypeData);
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    private function setDomains(ProductTypeData $productTypeData): void
    {
        foreach ($this->domains as $productTypeDomain) {
            $domainId = $productTypeDomain->getDomainId();
            $productTypeDomain->setFreeTransport($productTypeData->freeTransport[$domainId]);
            $productTypeDomain->setFreeTransportMinimalPrice($productTypeData->freeTransportMinimalPrice[$domainId]);
        }
    }

    /**
     * @param int $domainId
     * @return \App\Model\Product\Type\ProductTypeDomain
     */
    private function getProductTypeDomain(int $domainId): ProductTypeDomain
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        $message = sprintf('Product type domain for domain %s int product type with ID %s not found', $domainId, $this->getId());
        throw new ProductTypeDomainNotFoundException($message);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string
     */
    public function getAkeneoCode(): string
    {
        return $this->akeneoCode;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isFreeTransport(int $domainId): bool
    {
        return $this->getProductTypeDomain($domainId)->isFreeTransport();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getFreeTransportMinimalPrice(int $domainId): ?Money
    {
        return $this->getProductTypeDomain($domainId)->getFreeTransportMinimalPrice();
    }
}
