<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

use App\Model\SeoPage\Exception\SeoPageDomainNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private int $id;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private string $pageName;

    /**
     * @var \App\Model\SeoPage\SeoPageDomain[]|\Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="App\Model\SeoPage\SeoPageDomain", mappedBy="seoPage", cascade={"persist"})
     */
    private Collection $domains;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $defaultPage;

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
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
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     */
    public function edit(SeoPageData $seoPageData): void
    {
        $this->setData($seoPageData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoTitle(int $domainId): ?string
    {
        return  $this->getSeoPageDomain($domainId)->getSeoTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId): ?string
    {
        return  $this->getSeoPageDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getCanonicalUrl(int $domainId): ?string
    {
        return  $this->getSeoPageDomain($domainId)->getCanonicalUrl();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoOgTitle(int $domainId): ?string
    {
        return  $this->getSeoPageDomain($domainId)->getSeoOgTitle();
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getSeoOgDescription(int $domainId): ?string
    {
        return  $this->getSeoPageDomain($domainId)->getSeoOgDescription();
    }

    /**
     * @param int $domainId
     * @return \App\Model\SeoPage\SeoPageDomain
     */
    private function getSeoPageDomain(int $domainId): SeoPageDomain
    {
        foreach ($this->domains as $seoPageDomain) {
            if ($seoPageDomain->getDomainId() === $domainId) {
                return $seoPageDomain;
            }
        }

        throw new SeoPageDomainNotFoundException($this->id, $domainId);
    }

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     */
    private function setData(SeoPageData $seoPageData): void
    {
        $this->setDomains($seoPageData);
        $this->defaultPage = $seoPageData->defaultPage;
    }

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     */
    private function createDomains(SeoPageData $seoPageData): void
    {
        $domainIds = array_keys($seoPageData->seoTitlesIndexedByDomainId);

        foreach ($domainIds as $domainId) {
            $seoPageDomain = new SeoPageDomain($domainId, $this);
            $this->domains->add($seoPageDomain);
        }

        $this->setDomains($seoPageData);
    }

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     */
    private function setDomains(SeoPageData $seoPageData): void
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
    public function isDefaultPage(): bool
    {
        return $this->defaultPage;
    }
}
