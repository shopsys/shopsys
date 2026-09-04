<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;

class ProductDomainTest extends TestCase
{
    /**
     * @return iterable<string, array{orderingPriority: mixed, expectedOrderingPriority: int}>
     */
    public static function orderingPriorityProvider(): iterable
    {
        yield 'integer' => [
            'orderingPriority' => 5,
            'expectedOrderingPriority' => 5,
        ];

        yield 'numeric string' => [
            'orderingPriority' => '7',
            'expectedOrderingPriority' => 7,
        ];

        yield 'float' => [
            'orderingPriority' => 3.0,
            'expectedOrderingPriority' => 3,
        ];

        yield 'null' => [
            'orderingPriority' => null,
            'expectedOrderingPriority' => 0,
        ];
    }

    #[DataProvider('orderingPriorityProvider')]
    public function testOrderingPriorityIsCastToInteger(mixed $orderingPriority, int $expectedOrderingPriority): void
    {
        $productDomain = new ProductDomain(Product::create(TestProductProvider::getTestProductData()), Domain::FIRST_DOMAIN_ID);
        $productDomain->setOrderingPriority($orderingPriority);

        $this->assertSame($expectedOrderingPriority, $productDomain->getOrderingPriority());
    }
}
