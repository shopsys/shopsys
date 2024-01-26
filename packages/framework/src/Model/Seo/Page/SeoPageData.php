<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

class SeoPageData
{
    /**
     * @var string
     */
    public $pageName;

    /**
     * @var bool
     */
    public $defaultPage;

    /**
     * @var string[]|null[]
     */
    public $pageSlugsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public $seoTitlesIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public $seoMetaDescriptionsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public $canonicalUrlsIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public $seoOgTitlesIndexedByDomainId;

    /**
     * @var string[]|null[]
     */
    public $seoOgDescriptionsIndexedByDomainId;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $seoOgImage;

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
