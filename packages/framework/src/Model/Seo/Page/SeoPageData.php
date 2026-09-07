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
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData[]
     */
    public $seo;

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
        $this->seo = [];
        $this->seoOgTitlesIndexedByDomainId = [];
        $this->seoOgDescriptionsIndexedByDomainId = [];
        $this->defaultPage = false;
    }
}
