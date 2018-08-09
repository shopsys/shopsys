<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandDomainNotFoundException;

/**
 * @ORM\Table(name="brands")
 * @ORM\Entity
 */
class Brand extends AbstractTranslatableEntity
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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation")
     */
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandDomain[]|\Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Product\Brand\BrandDomain", mappedBy="brand", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    public function __construct(BrandData $brandData)
    {
        $this->name = $brandData->name;
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();

        $this->setTranslations($brandData);
        $this->createDomains($brandData);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function edit(BrandData $brandData)
    {
        $this->name = $brandData->name;
        $this->setTranslations($brandData);
        $this->setDomains($brandData);
    }

    protected function setTranslations(BrandData $brandData)
    {
        foreach ($brandData->descriptions as $locale => $description) {
            $brandTranslation = $this->translation($locale);
            /* @var $brandTranslation \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation */
            $brandTranslation->setDescription($description);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation
     */
    protected function createTranslation()
    {
        return new BrandTranslation();
    }

    protected function setDomains(BrandData $brandData)
    {
        foreach ($this->domains as $brandDomain) {
            $domainId = $brandDomain->getDomainId();
            $brandDomain->setSeoTitle($brandData->seoTitles[$domainId]);
            $brandDomain->setSeoH1($brandData->seoH1s[$domainId]);
            $brandDomain->setSeoMetaDescription($brandData->seoMetaDescriptions[$domainId]);
        }
    }

    protected function createDomains(BrandData $brandData)
    {
        $domainIds = array_keys($brandData->seoTitles);

        foreach ($domainIds as $domainId) {
            $brandDomain = new BrandDomain($this, $domainId);
            $this->domains[] = $brandDomain;
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

        throw new BrandDomainNotFoundException($this->id, $domainId);
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
