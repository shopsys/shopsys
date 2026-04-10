<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageDomainNotFoundException;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'seo_pages')]
#[ORM\Entity]
#[EntityImage]
#[EntityImage('og')]
class SeoPage
{
    public const string SEO_PAGE_HOMEPAGE_SLUG = '/';

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: false)]
    protected $pageName;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain>
     */
    #[ORM\OneToMany(targetEntity: SeoPageDomain::class, mappedBy: 'seoPage', cascade: ['persist'])]
    protected $domains;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected $defaultPage;

    public function __construct(
        SeoPageData $seoPageData,
    ) {
        $this->pageName = $seoPageData->pageName;
        $this->domains = new ArrayCollection();

        $this->createDomains($seoPageData);
        $this->setData($seoPageData);
    }

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
     * @return string|null
     */
    public function getSeoTitle(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoMetaDescription();
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getCanonicalUrl();
    }

    /**
     * @return string|null
     */
    public function getSeoOgTitle(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoOgTitle();
    }

    /**
     * @return string|null
     */
    public function getSeoOgDescription(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getSeoOgDescription();
    }

    /**
     * @return string
     */
    public function getPageSlug(int $domainId)
    {
        return $this->getSeoPageDomain($domainId)->getPageSlug();
    }

    protected function getSeoPageDomain(int $domainId): SeoPageDomain
    {
        foreach ($this->domains as $seoPageDomain) {
            if ($seoPageDomain->getDomainId() === $domainId) {
                return $seoPageDomain;
            }
        }

        throw new SeoPageDomainNotFoundException($this->id, $domainId);
    }

    protected function setData(SeoPageData $seoPageData): void
    {
        $this->setDomains($seoPageData);
        $this->defaultPage = $seoPageData->defaultPage;
    }

    protected function createDomains(SeoPageData $seoPageData): void
    {
        $domainIds = array_keys($seoPageData->seoTitlesIndexedByDomainId);

        foreach ($domainIds as $domainId) {
            $seoPageDomain = new SeoPageDomain($domainId, $this);
            $seoPageDomain->setPageSlug($seoPageData->pageSlugsIndexedByDomainId[$domainId]);
            $this->domains->add($seoPageDomain);
        }

        $this->setDomains($seoPageData);
    }

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
