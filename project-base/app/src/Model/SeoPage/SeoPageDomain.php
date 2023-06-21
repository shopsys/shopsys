<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

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
    private int $id;

    /**
     * @var int
     * @ORM\Column(name="domain_id", type="integer")
     */
    private int $domainId;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $seoTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $seoMetaDescription;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $canonicalUrl;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $seoOgTitle;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $seoOgDescription;

    /**
     * @var \App\Model\SeoPage\SeoPage
     * @ORM\ManyToOne(targetEntity="App\Model\SeoPage\SeoPage", inversedBy="domains")
     * @ORM\JoinColumn(name="seo_page_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    private SeoPage $seoPage;

    /**
     * @param int $domainId
     * @param \App\Model\SeoPage\SeoPage $seoPage
     */
    public function __construct(
        int $domainId,
        SeoPage $seoPage,
    ) {
        $this->domainId = $domainId;
        $this->seoPage = $seoPage;

        $this->seoTitle = null;
        $this->seoMetaDescription = null;
        $this->canonicalUrl = null;
        $this->seoOgTitle = null;
        $this->seoOgDescription = null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return \App\Model\SeoPage\SeoPage
     */
    public function getSeoPage(): SeoPage
    {
        return $this->seoPage;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    /**
     * @param string|null $seoMetaDescription
     */
    public function setSeoMetaDescription(?string $seoMetaDescription): void
    {
        $this->seoMetaDescription = $seoMetaDescription;
    }

    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    /**
     * @param string|null $canonicalUrl
     */
    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    /**
     * @return string|null
     */
    public function getSeoOgTitle(): ?string
    {
        return $this->seoOgTitle;
    }

    /**
     * @param string|null $seoOgTitle
     */
    public function setSeoOgTitle(?string $seoOgTitle): void
    {
        $this->seoOgTitle = $seoOgTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoOgDescription(): ?string
    {
        return $this->seoOgDescription;
    }

    /**
     * @param string|null $seoOgDescription
     */
    public function setSeoOgDescription(?string $seoOgDescription): void
    {
        $this->seoOgDescription = $seoOgDescription;
    }
}
