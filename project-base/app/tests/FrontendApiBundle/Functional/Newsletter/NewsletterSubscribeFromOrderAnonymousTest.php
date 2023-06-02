<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Newsletter;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NewsletterSubscribeFromOrderAnonymousTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade
     * @inject
     */
    private NewsletterFacade $newsletterFacade;

    private const ANONYMOUS_USER = 'user@example.com';

    public function testSubscribeFromOrderAnonymousUser(): void
    {
        $this->createOrder();

        $subscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(self::ANONYMOUS_USER, $this->domain->getId());

        $this->assertNotNull($subscriber);
        $this->assertEquals(self::ANONYMOUS_USER, $subscriber->getEmail());
    }

    private function createOrder(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
            'cartUuid' => $cartUuid,
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $transport->getPayments()[0]->getUuid(),
            'cartUuid' => $cartUuid,
        ]);

        $orderVariables = [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => self::ANONYMOUS_USER,
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'differentDeliveryAddress' => false,
            'newsletterSubscription' => true,
        ];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', $orderVariables);
    }
}
