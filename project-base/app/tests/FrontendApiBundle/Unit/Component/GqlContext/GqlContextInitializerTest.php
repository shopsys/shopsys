<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\GqlContext;

use App\FrontendApi\Component\GqlContext\GqlContextInitializer;
use ArrayObject;
use GraphQL\Type\SchemaConfig;
use Overblog\GraphQLBundle\Definition\Type\ExtensibleSchema;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use PHPUnit\Framework\TestCase;

class GqlContextInitializerTest extends TestCase
{
    private const CART_UUID = '81d7f8ba-e7e5-4ff6-8fc4-958c6012099d';

    public function testCartUuidArgumentInQuery(): void
    {
        $event = ExecutorArgumentsEvent::create(
            'cc',
            new ExtensibleSchema(SchemaConfig::create([])),
            'requestString',
            new ArrayObject(),
            null,
            [
                'cartUuid' => self::CART_UUID,
            ]
        );

        $contextInitializer = new GqlContextInitializer();
        $contextInitializer->initializeContext($event);

        $expectedContext = new ArrayObject([
            'args' => [
                'cartUuid' => self::CART_UUID,
            ],
        ]);

        $this->assertEquals($expectedContext, $event->getContextValue());
    }

    public function testCartUuidInInput(): void
    {
        $event = ExecutorArgumentsEvent::create(
            'cc',
            new ExtensibleSchema(SchemaConfig::create([])),
            'requestString',
            new ArrayObject(),
            null,
            [
                'input' => [
                    'cartUuid' => self::CART_UUID,
                ],
            ]
        );

        $contextInitializer = new GqlContextInitializer();
        $contextInitializer->initializeContext($event);

        $expectedContext = new ArrayObject([
            'args' => [
                'cartUuid' => self::CART_UUID,
            ],
        ]);

        $this->assertEquals($expectedContext, $event->getContextValue());
    }
}
