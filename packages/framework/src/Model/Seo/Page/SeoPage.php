<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageDomainNotFoundException;

/**
 * @ORM\Table(name="seo_pages")
 * @ORM\Entity
 */
class SeoPage
{
    public const SEO_PAGE_HOMEPAGE_SLUG = 'homepage';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $pageName;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain", mappedBy="seoPage", cascade={"persist"})
     */
    protected $domains;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $defaultPage;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     */
    public function __construct(
        SeoPageData $seoPageData,
    ) {
        $this->pageName = $seoPageData->pageName;
        $this->domains = new ArrayCollection();

        $this->createDomains($seoPageData);
        $this->setData($seoPageData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     */
    public function edit(SeoPageData $seoPageData): void
    {
        $this->setData($seoPageData);
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
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getCanonicalUrl(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getCanonicalUrl();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoOgTitle(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoOgTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoOgDescription(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoOgDescription();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain
     */
    protected function getSeoPageDomain(int $domainId): SeoPageDomain
    {
        foreach ($this->domains as $seoPageDomain) {
            if ($seoPageDomain->getDomainId() === $domainId) {
                return $seoPageDomain;
            }
        }

        throw new SeoPageDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     */
    protected function setData(SeoPageData $seoPageData): void
    {
        $this->setDomains($seoPageData);
        $this->defaultPage = $seoPageData->defaultPage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     */
    protected function createDomains(SeoPageData $seoPageData): void
    {
        $domainIds = array_keys($seoPageData->seoTitlesIndexedByDomainId);

        foreach ($domainIds as $domainId) {
            $seoPageDomain = new SeoPageDomain($domainId, $this);
            $this->domains->add($seoPageDomain);
        }

        $this->setDomains($seoPageData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     */
    protected function setDomains(SeoPageData $seoPageData): void
    {
        foreach ($this->domains as $seoPageDomain) {
            $domainId = $seoPageDomain->getDomainId();

            $seoPageDomain->setSeoTitle($seoPageData->seoTitlesIndexedByDomainId[$domainId]);
            $seoPageDomain->setSeoMetaDescription($seoPageData->seoMetaDescriptionsIndexedByDomainId[$domainId]);
            $seoPageDomain->setCanonicalUrl($seoPageData->canonicalUrlsIndexedByDomainId[$domainId]);
            $seoPageDomain->setSeoOgTitle($seoPageData->seoOgTitlesIndexedByDomainId[$domainId]);
            $seoPageDomain->setSeoOgDescription($seoPageData->seoOgDescriptionsIndexedByDomainId[$domainId]);
        }
    }

    /**
     * @return bool
     */
    public function isDefaultPage()
    {
        return $this->defaultPage;
    }
}
