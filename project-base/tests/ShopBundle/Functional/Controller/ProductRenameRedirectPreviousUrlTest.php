<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Controller;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductRenameRedirectPreviousUrlTest extends TransactionFunctionalTestCase
{
    private const TESTED_PRODUCT_ID = 1;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductDataFactory
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
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::TESTED_PRODUCT_ID);

        $previousFriendlyUrlSlug = $this->friendlyUrlFacade->findMainFriendlyUrl(1, 'front_product_detail', self::TESTED_PRODUCT_ID)->getSlug();

        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->name[$this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()] = 'rename';

        $this->productFacade->edit(self::TESTED_PRODUCT_ID, $productData);

        $client = $this->getClient();
        $client->request('GET', '/' . $previousFriendlyUrlSlug);

        // Should be 301 (moved permanently), because old product urls should be permanently redirected
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }
}
