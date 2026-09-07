<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'seo_page_domains')]
#[ORM\UniqueConstraint(name: 'seo_page_domain', columns: ['seo_page_id', 'domain_id'])]
#[ORM\UniqueConstraint(name: 'idx_seo_page_domain_slug', columns: ['domain_id', 'page_slug'])]
#[ORM\Entity]
class SeoPageDomain
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(name: 'domain_id', type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    #[AsMcpColumn]
    #[ORM\Embedded(class: SeoAttributes::class)]
    protected $seo;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $seoOgTitle;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $seoOgDescription;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $pageSlug;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'seo_page_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: SeoPage::class, inversedBy: 'domains')]
    protected $seoPage;

    public function __construct(
        int $domainId,
        SeoPage $seoPage,
    ) {
        $this->domainId = $domainId;
        $this->seoPage = $seoPage;
        $this->seo = new SeoAttributes();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     */
    public function getSeoPage()
    {
        return $this->seoPage;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    public function getSeoAttributes()
    {
        return $this->seo;
    }

    /**
     * @return string|null
     */
    public function getSeoOgTitle()
    {
        return $this->seoOgTitle;
    }

    /**
     * @param string|null $seoOgTitle
     */
    public function setSeoOgTitle($seoOgTitle): void
    {
        $this->seoOgTitle = $seoOgTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoOgDescription()
    {
        return $this->seoOgDescription;
    }

    /**
     * @param string|null $seoOgDescription
     */
    public function setSeoOgDescription($seoOgDescription): void
    {
        $this->seoOgDescription = $seoOgDescription;
    }

    /**
     * @return string
     */
    public function getPageSlug()
    {
        return $this->pageSlug;
    }

    /**
     * @param string $pageSlug
     */
    public function setPageSlug($pageSlug): void
    {
        $this->pageSlug = $pageSlug;
    }
}
