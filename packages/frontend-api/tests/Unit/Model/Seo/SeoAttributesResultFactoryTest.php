<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Seo;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;
use Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesResultFactory;

class SeoAttributesResultFactoryTest extends TestCase
{
    private SeoAttributesResultFactory $seoAttributesResultFactory;

    #[Override]
    protected function setUp(): void
    {
        $this->seoAttributesResultFactory = new SeoAttributesResultFactory();
    }

    public function testAllAttributesAreCopiedFromSeoAttributes(): void
    {
        $seoAttributesData = (new SeoAttributesDataFactory())->create();
        $seoAttributesData->title = 'Title';
        $seoAttributesData->metaDescription = 'Meta description';
        $seoAttributesData->h1 = 'Heading';
        $seoAttributesData->metaRobots = 'noindex';
        $seoAttributesData->canonicalUrl = 'https://example.com/canonical';
        $seoAttributes = new SeoAttributes();
        $seoAttributes->edit($seoAttributesData);

        $result = $this->seoAttributesResultFactory->createFromSeoAttributes($seoAttributes, '<p>Fallback</p>');

        $this->assertSame('Title', $result->title);
        $this->assertSame('Meta description', $result->metaDescription);
        $this->assertSame('Heading', $result->h1);
        $this->assertSame('noindex', $result->metaRobots);
        $this->assertSame('https://example.com/canonical', $result->canonicalUrl);
    }

    public function testMetaDescriptionFallsBackToPlainTextOfDescription(): void
    {
        $result = $this->seoAttributesResultFactory->create(
            null,
            '   ',
            null,
            null,
            null,
            '<p>Lorem <strong>ipsum</strong> &amp; dolor</p>',
        );

        $this->assertSame('Lorem ipsum & dolor', $result->metaDescription);
    }

    public function testFallbackMetaDescriptionIsTruncatedToWholeWords(): void
    {
        $longDescription = str_repeat('word ', 40) . 'tail';

        $result = $this->seoAttributesResultFactory->create(null, null, null, null, null, $longDescription);

        $this->assertLessThanOrEqual(SeoAttributesResultFactory::META_DESCRIPTION_MAX_LENGTH, mb_strlen($result->metaDescription));
        $this->assertStringEndsWith('word', $result->metaDescription);
    }

    public function testMetaDescriptionIsNullWithoutAnySource(): void
    {
        $result = $this->seoAttributesResultFactory->create(null, null, null, null, null, '<p> </p>');

        $this->assertNull($result->metaDescription);
    }
}
