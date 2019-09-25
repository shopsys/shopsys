<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Controller;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductRenameRedirectPreviousUrlTest extends TransactionFunctionalTestCase
{
    private const TESTED_PRODUCT_ID = 1;

    public function testPreviousUrlRedirect(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade */
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory $productDataFactory */
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::TESTED_PRODUCT_ID);

        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade */
        $friendlyUrlFacade = $this->getContainer()->get(FriendlyUrlFacade::class);
        $previousFriendlyUrlSlug = $friendlyUrlFacade->findMainFriendlyUrl(1, 'front_product_detail', self::TESTED_PRODUCT_ID)->getSlug();

        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $productData = $productDataFactory->createFromProduct($product);
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);
        $productData->name[$domain->getDomainConfigById(1)->getLocale()] = 'rename';

        $productFacade->edit(self::TESTED_PRODUCT_ID, $productData);

        $client = $this->getClient();
        $client->request('GET', '/' . $previousFriendlyUrlSlug);

        // Should be 301 (moved permanently), because old product urls should be permanently redirected
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }
}
