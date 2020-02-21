<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductRenameRedirectPreviousUrlTest extends TransactionFunctionalTestCase
{
    private const TESTED_PRODUCT_ID = 1;

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
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::TESTED_PRODUCT_ID);

        $previousFriendlyUrlSlug = $this->friendlyUrlFacade->findMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, 'front_product_detail', self::TESTED_PRODUCT_ID)->getSlug();

        /** @var \App\Model\Product\Product $product */
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->name[$this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()] = 'rename';

        $this->productFacade->edit(self::TESTED_PRODUCT_ID, $productData);

        $client = $this->findClient();
        $client->request('GET', '/' . $previousFriendlyUrlSlug);

        // Should be 301 (moved permanently), because old product urls should be permanently redirected
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }
}
