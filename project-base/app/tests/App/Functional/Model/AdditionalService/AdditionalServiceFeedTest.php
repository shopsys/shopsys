<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\AdditionalService;

use App\DataFixtures\Demo\ProductDataFixture;
use League\Flysystem\FilesystemOperator;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\ZboziServiceTypeEnum;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdditionalServiceFeedTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private FeedFacade $feedFacade;

    /**
     * @inject
     */
    private FilesystemOperator $filesystem;

    /**
     * @inject
     */
    private AdditionalServiceFacade $additionalServiceFacade;

    /**
     * @inject
     */
    private AdditionalServiceDataFactory $additionalServiceDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = 'SERVICE-FEED';
        $additionalServiceData->zboziServiceType = ZboziServiceTypeEnum::FREE_INSTALLATION;

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Feed test service';
            $additionalServiceData->feedName[$locale] = 'Assembly included';
            $additionalServiceData->zboziDescription[$locale] = 'Professional assembly of the product';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(100);
        }

        $additionalService = $this->additionalServiceFacade->create($additionalServiceData);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID] = [$additionalService];
        $this->productFacade->edit($product->getId(), $productData);
    }

    public function testHeurekaFeedContainsSpecialService(): void
    {
        $feedContent = $this->generateFeedContent('heureka');

        $this->assertStringContainsString('<SPECIAL_SERVICE>Assembly included</SPECIAL_SERVICE>', $feedContent);
        $this->assertStringNotContainsString('Damage insurance for 2 years', $feedContent);
    }

    public function testZboziFeedContainsExtraMessageAndCustomText(): void
    {
        $feedContent = $this->generateFeedContent('zbozi');

        $this->assertStringContainsString('<EXTRA_MESSAGE>free_installation</EXTRA_MESSAGE>', $feedContent);
        $this->assertStringContainsString('<CUSTOM_TEXT>Professional assembly of the product</CUSTOM_TEXT>', $feedContent);
        $this->assertStringNotContainsString('Damage insurance for 2 years', $feedContent);
    }

    public function testGoogleFeedContainsCustomLabel0(): void
    {
        $feedContent = $this->generateFeedContent('google');

        $this->assertStringContainsString('<g:custom_label_0>Assembly included</g:custom_label_0>', $feedContent);
    }

    public function testMergadoFeedContainsAdditionalServiceElements(): void
    {
        $feedContent = $this->generateFeedContent('mergado');

        $this->assertStringContainsString('<SPECIAL_SERVICE>Assembly included</SPECIAL_SERVICE>', $feedContent);
        $this->assertStringContainsString('<EXTRA_MESSAGE>free_installation</EXTRA_MESSAGE>', $feedContent);
        $this->assertStringContainsString('<CUSTOM_TEXT>Professional assembly of the product</CUSTOM_TEXT>', $feedContent);
    }

    private function generateFeedContent(string $feedName): string
    {
        $domainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
        $this->feedFacade->generateFeed($feedName, $domainConfig);

        $feedInfo = null;

        foreach ($this->feedFacade->getFeedsInfo() as $existingFeedInfo) {
            if ($existingFeedInfo->getName() === $feedName) {
                $feedInfo = $existingFeedInfo;

                break;
            }
        }

        $this->assertThat($feedInfo, $this->logicalNot($this->isNull()));

        $feedFilepath = $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig);
        $this->assertTrue($this->filesystem->has($feedFilepath));

        $feedContent = $this->filesystem->read($feedFilepath);
        $this->filesystem->delete($feedFilepath);

        return $feedContent;
    }
}
