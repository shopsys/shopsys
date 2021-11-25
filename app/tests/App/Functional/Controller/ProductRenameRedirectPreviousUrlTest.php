<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use App\DataFixtures\Demo\ProductDataFixture;
use Faker\Provider\Text;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\ApplicationTestCase;

class ProductRenameRedirectPreviousUrlTest extends ApplicationTestCase
{
    private const TESTED_PRODUCT_ID = 100;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     * @inject
     */
    private $friendlyUrlFacade;

    public function testPreviousUrlRedirect(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::TESTED_PRODUCT_ID);

        $previousFriendlyUrlSlug = $this->friendlyUrlFacade->findMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, 'front_product_detail', self::TESTED_PRODUCT_ID)->getSlug();

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->name[$this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()] = Text::asciify();

        $this->productFacade->edit(self::TESTED_PRODUCT_ID, $productData);

        $client = $this->getCurrentClient();
        $client->request('GET', '/' . $previousFriendlyUrlSlug);

        // Should be 301 (moved permanently), because old product urls should be permanently redirected
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }
}
