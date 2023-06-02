<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\GqlContext;

use App\FrontendApi\Component\GqlContext\GqlContextHelper;
use ArrayObject;
use PHPUnit\Framework\TestCase;

class GqlContextHelperTest extends TestCase
{
    private const CART_UUID = '81d7f8ba-e7e5-4ff6-8fc4-958c6012099d';

    public function testGetArgsFromContext(): void
    {
        $args = GqlContextHelper::getArgs(
            new ArrayObject([
                'args' => [
                    'cartUuid' => self::CART_UUID,
                ],
            ])
        );

        $expectedContext = new ArrayObject([
            'cartUuid' => self::CART_UUID,
        ]);

        $this->assertEquals($expectedContext, $args);
    }

    public function testContextIsNull(): void
    {
        $args = GqlContextHelper::getArgs(null);

        $expectedContext = new ArrayObject();

        $this->assertEquals($expectedContext, $args);
    }

    public function testGetCartUuidFromContext(): void
    {
        $context = new ArrayObject([
            'args' => [
                'cartUuid' => self::CART_UUID,
            ],
        ]);

        $this->assertEquals(self::CART_UUID, GqlContextHelper::getCartUuid($context));
    }
}
