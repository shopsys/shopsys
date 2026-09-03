<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

class SeoAttributesDataFactory
{
    protected function createInstance(): SeoAttributesData
    {
        return new SeoAttributesData();
    }

    public function create(): SeoAttributesData
    {
        return $this->createInstance();
    }

    public function createFromSeoAttributes(SeoAttributes $seoAttributes): SeoAttributesData
    {
        $seoAttributesData = $this->createInstance();

        $seoAttributesData->title = $seoAttributes->getTitle();
        $seoAttributesData->metaDescription = $seoAttributes->getMetaDescription();
        $seoAttributesData->h1 = $seoAttributes->getH1();

        return $seoAttributesData;
    }
}
