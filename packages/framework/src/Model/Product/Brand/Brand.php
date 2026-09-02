<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandDomainNotFoundException;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'brands')]
#[ORM\Entity]
#[EntityImage]
class Brand extends AbstractTranslatableEntity
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation>
     */
    #[Prezent\Translations(targetEntity: BrandTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Brand\BrandDomain>
     */
    #[ORM\OneToMany(targetEntity: BrandDomain::class, mappedBy: 'brand', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    public function __construct(BrandData $brandData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();

        $this->uuid = $brandData->uuid;

        $this->createDomains($brandData);
        $this->setData($brandData);
    }

    public function edit(BrandData $brandData): void
    {
        $this->setDomains($brandData);
        $this->setData($brandData);
    }

    protected function setData(BrandData $brandData): void
    {
        $this->name = $brandData->name;
        $this->setTranslations($brandData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    protected function setTranslations(BrandData $brandData): void
    {
        foreach ($brandData->descriptions as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new BrandTranslation();
    }

    protected function setDomains(BrandData $brandData): void
    {
        foreach ($this->domains as $brandDomain) {
            $domainId = $brandDomain->getDomainId();
            $brandDomain->setSeoTitle($brandData->seoTitles[$domainId]);
            $brandDomain->setSeoH1($brandData->seoH1s[$domainId]);
            $brandDomain->setSeoMetaDescription($brandData->seoMetaDescriptions[$domainId]);
        }
    }

    protected function createDomains(BrandData $brandData): void
    {
        $domainIds = array_keys($brandData->seoTitles);

        foreach ($domainIds as $domainId) {
            $brandDomain = new BrandDomain($this, $domainId);
            $this->domains->add($brandDomain);
        }

        $this->setDomains($brandData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandDomain
     */
    protected function getBrandDomain(int $domainId)
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        throw new BrandDomainNotFoundException($domainId, $this->id);
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getBrandDomain($domainId)->getSeoTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getBrandDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @return string|null
     */
    public function getSeoH1(int $domainId)
    {
        return $this->getBrandDomain($domainId)->getSeoH1();
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getDescription($locale = null)
    {
        return $this->translation($locale)->getDescription();
    }
}
