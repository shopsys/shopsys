<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Product;

use DateTime;
use PhpAmqpLib\Message\AMQPMessage;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Visibility\ProductVisibilityRecalculateConsumerInterface;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class ProductVisibilityRecalculateConsumerTest extends TransactionFunctionalTestCase
{
    public function testConsumerChangesVisibilityOfProduct()
    {
        $productId = 4;

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface $productDataFactory */
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);

        /** @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade */
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Visibility\ProductVisibilityRecalculateConsumerInterface $productVisibilityRecalculateConsumer */
        $productVisibilityRecalculateConsumer = $this->getContainer()->get(ProductVisibilityRecalculateConsumerInterface::class);

        $productData = $productDataFactory->createFromProduct($product);
        $productData->sellingTo = (new DateTime('-1 day'));

        $productFacade->edit($product->getId(), $productData);

        $message = new AMQPMessage($productId);

        $productVisibilityRecalculateConsumer->execute($message);

        $this->getEntityManager()->clear(Product::class);

        $recalculatedProduct = $productFacade->getById($productId);

        $this->assertSame(false, $recalculatedProduct->isVisible());
    }
}
