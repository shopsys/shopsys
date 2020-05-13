<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

class DynamicFieldsInOrderTest extends AbstractOrderTestCase
{
    public function testHasDynamicFields(): void
    {
        $orderMutation = $this->getOrderMutation(__DIR__ . '/Resources/dynamicFieldsInOrder.graphql');

        $responseData = $this->getResponseContentForQuery($orderMutation)['data']['CreateOrder'];

        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertIsString($responseData['uuid']);
        $this->assertArrayHasKey('number', $responseData);
        $this->assertIsString($responseData['number']);
        $this->assertArrayHasKey('urlHash', $responseData);
        $this->assertIsString($responseData['urlHash']);
        $this->assertArrayHasKey('creationDate', $responseData);
        $this->assertIsString($responseData['creationDate']);
    }
}
