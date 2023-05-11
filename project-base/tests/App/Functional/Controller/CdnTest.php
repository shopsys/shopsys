<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Tests\App\Test\FunctionalTestCase;

class CdnTest extends FunctionalTestCase
{
    private ?string $originalCdnDomain;

    public function testImageHasCdnUrl(): void
    {
        $cdnDomain = 'https://cdn.example.com';
        $this->originalCdnDomain = $_ENV['CDN_DOMAIN'] ?? null;

        $_ENV['CDN_DOMAIN'] = $cdnDomain;
        $imageFacade = self::getContainer()->get(ImageFacade::class);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $productImageUrl = $imageFacade->getImageUrl(
            $this->domain->getCurrentDomainConfig(),
            $product,
        );
        $this->assertStringStartsWith($cdnDomain, $productImageUrl);

        $productImageUrl = $imageFacade->getImageUrlFromAttributes(
            $this->domain->getCurrentDomainConfig(),
            $product->getId(),
            'jpg',
            'product',
            null,
        );
        $this->assertStringStartsWith($cdnDomain, $productImageUrl);

        $additionalImagesData = $imageFacade->getAdditionalImagesDataFromAttributes(
            $this->domain->getCurrentDomainConfig(),
            $product->getId(),
            'jpg',
            'product',
            null,
        );

        foreach ($additionalImagesData as $additionalImageData) {
            $this->assertStringStartsWith($cdnDomain, $additionalImageData->url);
        }

        $additionalImagesData = $imageFacade->getAdditionalImagesData(
            $this->domain->getCurrentDomainConfig(),
            $product,
            null,
            null,
        );

        foreach ($additionalImagesData as $additionalImageData) {
            $this->assertStringStartsWith($cdnDomain, $additionalImageData->url);
        }
    }

    public function tearDown(): void
    {
        $_ENV['CDN_DOMAIN'] = $this->originalCdnDomain;

        parent::tearDown();
    }
}
