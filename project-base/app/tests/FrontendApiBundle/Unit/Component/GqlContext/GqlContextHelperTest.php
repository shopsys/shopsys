<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\GqlContext;

use ArrayObject;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;

class GqlContextHelperTest extends TestCase
{
    private const string CART_UUID = '81d7f8ba-e7e5-4ff6-8fc4-958c6012099d';

    private GqlContextHelper $gqlContextHelper;

    #[Override]
    protected function setUp(): void
    {
        $this->gqlContextHelper = new GqlContextHelper();

        parent::setUp();
    }

    public function testGetArgsFromContext(): void
    {
        $args = $this->gqlContextHelper->getArgs(
            new ArrayObject([
                'args' => [
                    'cartUuid' => self::CART_UUID,
                ],
            ]),
        );

        $expectedContext = new ArrayObject([
            'cartUuid' => self::CART_UUID,
        ]);

        $this->assertEquals($expectedContext, $args);
    }

    public function testContextIsNull(): void
    {
        $args = $this->gqlContextHelper->getArgs(null);

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

        $this->assertEquals(self::CART_UUID, $this->gqlContextHelper->getCartUuid($context));
    }
}
