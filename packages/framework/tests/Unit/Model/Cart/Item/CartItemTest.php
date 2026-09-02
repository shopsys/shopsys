<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Cart\Item;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class CartItemTest extends TestCase
{
    /**
     * @return iterable<string, array{quantity: int}>
     */
    public static function invalidQuantityProvider(): iterable
    {
        yield 'zero' => [
            'quantity' => 0,
        ];

        yield 'negative' => [
            'quantity' => -1,
        ];
    }

    #[DataProvider('invalidQuantityProvider')]
    public function testInvalidQuantityIsRejectedOnConstruct(int $quantity): void
    {
        $this->expectException(InvalidQuantityException::class);

        new CartItem($this->createCart(), $this->createProduct(), $quantity, Money::zero());
    }

    #[DataProvider('invalidQuantityProvider')]
    public function testInvalidQuantityIsRejectedOnChange(int $quantity): void
    {
        $cartItem = new CartItem($this->createCart(), $this->createProduct(), 1, Money::zero());

        $this->expectException(InvalidQuantityException::class);

        $cartItem->changeQuantity($quantity);
    }

    public function testValidQuantityIsStored(): void
    {
        $cartItem = new CartItem($this->createCart(), $this->createProduct(), 1, Money::zero());
        $cartItem->changeQuantity(5);

        $this->assertSame(5, $cartItem->getQuantity());
    }

    protected function createCart(): Cart
    {
        return new Cart('cartIdentifier', null);
    }

    protected function createProduct(): Product
    {
        return Product::create(TestProductProvider::getTestProductData());
    }
}
