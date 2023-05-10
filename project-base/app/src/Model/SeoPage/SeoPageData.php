<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;

class SeoPageData
{
    /**
     * @var string
     */
    public string $pageName;

    /**
     * @var bool
     */
    public bool $defaultPage;

    /**
     * @var string[]|null[]
     */
    public array $pageSlugsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public array $seoTitlesIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public array $seoMetaDescriptionsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public array $canonicalUrlsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public array $seoOgTitlesIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public array $seoOgDescriptionsIndexedByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public ImageUploadData $seoOgImage;

    public function __construct()
    {
        $this->pageSlugsIndexedByDomainId = [];
        $this->seoTitlesIndexedByDomainId = [];
        $this->seoMetaDescriptionsIndexedByDomainId = [];
        $this->canonicalUrlsIndexedByDomainId = [];
        $this->seoOgTitlesIndexedByDomainId = [];
        $this->seoOgDescriptionsIndexedByDomainId = [];
        $this->defaultPage = false;
    }
}
