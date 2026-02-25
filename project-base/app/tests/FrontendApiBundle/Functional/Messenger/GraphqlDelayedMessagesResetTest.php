<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Messenger;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tests\App\Test\Messenger\DummyMessage;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GraphqlDelayedMessagesResetTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private MessageBusInterface $messageBus;

    /**
     * @inject messenger.transport.dummy_transport
     */
    private InMemoryTransport $inMemoryTransport;

    public function testDelayedMessagesAreDispatchedOnSuccessfulGraphqlQuery(): void
    {
        $dummyTransport = $this->prepareDummyTransportWithDelayedMessage();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../Product/ProductList/graphql/ProductListQuery.graphql',
            [
                'uuid' => Uuid::uuid4()->toString(),
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertCount(1, $dummyTransport->getSent());
    }

    public function testDelayedMessagesAreResetOnGraphqlQueryError(): void
    {
        $dummyTransport = $this->prepareDummyTransportWithDelayedMessage();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../Order/graphql/GetOrderQuery.graphql',
            [
                'urlHash' => 'non-existent-order-hash',
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $this->assertCount(0, $dummyTransport->getSent());
    }

    public function testDelayedMessagesAreResetOnGraphqlMutationError(): void
    {
        $dummyTransport = $this->prepareDummyTransportWithDelayedMessage();

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 69, Product::class);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../HttpFoundation/graphql/AddProductToListAndFailMutation.graphql',
            [
                'productUuid' => $product->getUuid(),
                'notExistingProductUuid' => Uuid::uuid4()->toString(),
                'productListUuid' => Uuid::uuid4()->toString(),
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $this->assertCount(0, $dummyTransport->getSent());
    }

    private function prepareDummyTransportWithDelayedMessage(): InMemoryTransport
    {
        $this->inMemoryTransport->reset();

        $this->messageBus->dispatch(new DummyMessage());
        $this->assertCount(0, $this->inMemoryTransport->getSent());

        return $this->inMemoryTransport;
    }
}
