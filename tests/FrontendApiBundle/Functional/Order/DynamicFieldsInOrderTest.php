<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;

class DynamicFieldsInOrderTest extends AbstractOrderTestCase
{
    public function testHasDynamicFields(): void
    {
        $graphQlType = 'CreateOrder';
        $response = $this->getResponseContentForQuery($this->getMutation());

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertIsString($responseData['uuid']);

        $this->assertArrayHasKey('number', $responseData);
        $this->assertIsString($responseData['number']);

        $this->assertArrayHasKey('urlHash', $responseData);
        $this->assertIsString($responseData['urlHash']);

        $this->assertArrayHasKey('creationDate', $responseData);
        $this->assertIsString($responseData['creationDate']);
    }

    /**
     * @return string
     */
    private function getMutation(): string
    {
        $domainId = $this->domain->getId();
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatHigh */
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vatZero */
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $product1UnitPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('2891.70', $vatHigh);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment $paymentCashOnDelivery */
        $paymentCashOnDelivery = $this->getReference(PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY);
        $paymentPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('50', $vatZero);

        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport $transportCzechPost */
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $transportPrice = $this->getMutationPriceConvertedToDomainDefaultCurrency('100', $vatHigh);

        return 'mutation {
                    CreateOrder(
                        input: {
                            firstName: "firstName"
                            lastName: "lastName"
                            email: "user@example.com"
                            telephone: "+53 123456789"
                            onCompanyBehalf: true
                            companyName: "Airlocks s.r.o."
                            companyNumber: "1234"
                            companyTaxNumber: "EU4321"
                            street: "123 Fake Street"
                            city: "Springfield"
                            postcode: "12345"
                            country: "CZ"
                            note:"Thank You"
                            payment: {
                                uuid: "' . $paymentCashOnDelivery->getUuid() . '"
                                price: ' . $paymentPrice . '
                            }
                            transport: {
                                uuid: "' . $transportCzechPost->getUuid() . '"
                                price: ' . $transportPrice . '
                            }
                            differentDeliveryAddress: true
                            deliveryFirstName: "deliveryFirstName"
                            deliveryLastName: "deliveryLastName"
                            deliveryStreet: "deliveryStreet"
                            deliveryCity: "deliveryCity"
                            deliveryCountry: "SK"
                            deliveryPostcode: "13453"
                            products: [
                                {
                                    uuid: "' . $product1->getUuid() . '",
                                    unitPrice: ' . $product1UnitPrice . ',
                                    quantity: 10
                                },
                            ]
                        }
                    ) {
                        uuid
                        number
                        urlHash
                        creationDate
                    }
                }';
    }
}
