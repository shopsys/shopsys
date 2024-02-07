<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="seo_page_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="seo_page_domain", columns={"seo_page_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class SeoPageDomain
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="domain_id", type="integer")
     */
    protected $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $canonicalUrl;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoOgTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $seoOgDescription;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage", inversedBy="domains")
     * @ORM\JoinColumn(name="seo_page_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    protected $seoPage;

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     */
    public function __construct(
        int $domainId,
        SeoPage $seoPage,
    ) {
        $this->domainId = $domainId;
        $this->seoPage = $seoPage;
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
     * @return string|null
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle($seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription($seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        return $this->canonicalUrl;
    }

    /**
     * @param string|null $canonicalUrl
     */
    public function setCanonicalUrl($canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
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
}
