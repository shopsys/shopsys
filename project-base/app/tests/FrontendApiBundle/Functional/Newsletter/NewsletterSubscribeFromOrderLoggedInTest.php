<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Newsletter;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class NewsletterSubscribeFromOrderLoggedInTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private NewsletterFacade $newsletterFacade;

    public function testSubscribeFromOrderLoggedInUser(): void
    {
        // default user is already subscribed
        $this->createOrder(false);

        $subscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(self::DEFAULT_USER_EMAIL, $this->domain->getId());
        $this->assertNull($subscriber);

        $this->createOrder();
        $subscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(self::DEFAULT_USER_EMAIL, $this->domain->getId());

        $this->assertNotNull($subscriber);
        $this->assertEquals(self::DEFAULT_USER_EMAIL, $subscriber->getEmail());
    }

    /**
     * @param bool $newsletterSubscription
     */
    private function createOrder(bool $newsletterSubscription = true): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'transportUuid' => $transport->getUuid(),
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'paymentUuid' => $transport->getPayments()[0]->getUuid(),
        ]);

        $orderVariables = [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => self::DEFAULT_USER_EMAIL,
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'isDeliveryAddressDifferentFromBilling' => false,
            'newsletterSubscription' => $newsletterSubscription,
        ];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', $orderVariables);
    }
}
