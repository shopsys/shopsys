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
        // ensure the services will be created newly with the new CDN domain
        self::ensureKernelShutdown();
        self::bootKernel();

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

        $productImageUrl = $imageFacade->getImageUrl(
            $this->domain->getCurrentDomainConfig(),
            $product,
        );

        $this->assertStringStartsWith($cdnDomain, $productImageUrl);
    }

    public function tearDown(): void
    {
        $_ENV['CDN_DOMAIN'] = $this->originalCdnDomain;

        $imageCache = self::getContainer()->get('image_cache');
        $imageCache->clear();

        parent::tearDown();
    }
}
