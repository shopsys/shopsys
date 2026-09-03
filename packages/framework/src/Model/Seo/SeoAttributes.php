<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class SeoAttributes
{
    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $title;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $metaDescription;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $h1;

    public function edit(SeoAttributesData $seoAttributesData): void
    {
        $this->title = $seoAttributesData->title;
        $this->metaDescription = $seoAttributesData->metaDescription;
        $this->h1 = $seoAttributesData->h1;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return string|null
     */
    public function getH1()
    {
        return $this->h1;
    }
}
