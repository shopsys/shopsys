<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportGroupDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Shopsys\FrameworkBundle\Model\Transport\TransportUnavailabilityReasonInCartEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class TransportsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testByCartUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        $locale = $this->getFirstDomainLocale();
        // "Drone delivery" is excluded for a product in the demo cart, so it is now shown disabled and sorted last
        $expectedTransportsData = [
            ['name' => t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
        ];
        $this->assertCount(count($expectedTransportsData), $responseData);

        foreach ($expectedTransportsData as $key => $expectedTransport) {
            $this->assertSame($expectedTransport['name'], $responseData[$key]['name']);
        }
    }

    public function testTransports(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');
        $domainId = $this->domain->getId();
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);
        $vatZero = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId, Vat::class);

        $deliveryToAddressGroup = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS, TransportGroup::class);
        $pickupPointGroup = $this->getReference(TransportGroupDataFixture::TRANSPORT_GROUP_PICKUP_POINT, TransportGroup::class);

        $arrayExpected = [
            [
                'name' => t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'Packeta delivery company',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instructions' => t('Probably best value for your money', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'position' => 0,
                'daysUntilDelivery' => 2,
                'transportTypeCode' => TransportTypeEnum::TYPE_PACKETERY,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('49.95', $vatHigh),
                'images' => [
                    [
                        'url' => $this->getBaseUrlPath('/content-test/images/transport/715.png'),
                        'name' => t('Packeta', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    ],
                ],
                'payments' => [
                    ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Bank transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
                'vatPercent' => '21.000000',
                'group' => $this->getExpectedTransportGroupData($pickupPointGroup, 722),
            ],
            [
                'name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => null,
                'instructions' => null,
                'position' => 1,
                'daysUntilDelivery' => 4,
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('200', $vatHigh),
                'images' => [
                    [
                        'url' => $this->getBaseUrlPath('/content-test/images/transport/712.png'),
                        'name' => t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    ],
                ],
                'payments' => [
                    ['name' => t('GoPay - Payment By Card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Bank transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
                'vatPercent' => '21.000000',
                'group' => $this->getExpectedTransportGroupData($deliveryToAddressGroup, 721),
            ],
            [
                'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t('Czech state post service.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'instructions' => t('the Czech Post will try to deliver your parcel on time, but it will not succeed and despite the constant presence of your person at home, it will not catch you and you will have to pick up the parcel personally at the counter. Here, however, you have to endure an endlessly long line and an eternally grumpy lady postman.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'position' => 2,
                'daysUntilDelivery' => 5,
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
                'images' => [
                    [
                        'url' => $this->getBaseUrlPath('/content-test/images/transport/711.png'),
                        'name' => t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    ],
                ],
                'payments' => [
                    ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Bank transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
                'vatPercent' => '21.000000',
                'group' => $this->getExpectedTransportGroupData($deliveryToAddressGroup, 721),
            ],
            [
                'name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'You will be welcomed by friendly staff!',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instructions' => t('We are looking forward to your visit.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'position' => 3,
                'daysUntilDelivery' => 0,
                'transportTypeCode' => TransportTypeEnum::TYPE_PERSONAL_PICKUP,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                'images' => [
                    [
                        'url' => $this->getBaseUrlPath('/content-test/images/transport/713.png'),
                        'name' => t('Personal collection', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    ],
                ],
                'payments' => [
                    ['name' => t('GoPay - Payment By Card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Cash', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => [
                    'edges' => [
                        [
                            'node' => [
                                'name' => t('Ostrava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Pardubice', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Brno', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Praha', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Hradec Králové', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Olomouc', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Liberec', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                        [
                            'node' => [
                                'name' => t('Plzeň', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                            ],
                        ],
                    ],
                ],
                'vatPercent' => '21.000000',
                'group' => $this->getExpectedTransportGroupData($pickupPointGroup, 722),
            ],
            [
                'name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'description' => t(
                    'Suitable for all kinds of goods',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $this->getLocaleForFirstDomain(),
                ),
                'instructions' => t('Expect delivery by the end of next month', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'position' => 4,
                'daysUntilDelivery' => 0,
                'transportTypeCode' => TransportTypeEnum::TYPE_COMMON,
                'price' => $this->getSerializedPriceConvertedToDomainDefaultCurrency('0', $vatZero),
                'images' => [
                    [
                        'url' => $this->getBaseUrlPath('/content-test/images/transport/714.png'),
                        'name' => t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                    ],
                ],
                'payments' => [
                    ['name' => t('GoPay - Quick Bank Account Transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Bank transfer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                    ['name' => t('Pay later', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
                ],
                'stores' => null,
                'vatPercent' => '21.000000',
                'group' => $this->getExpectedTransportGroupData($deliveryToAddressGroup, 721),
            ],
        ];

        $this->assertSame($arrayExpected, $responseData);
    }

    /**
     * @return array<string, mixed>
     */
    private function getExpectedTransportGroupData(TransportGroup $transportGroup, int $imageId): array
    {
        $name = $transportGroup->getName($this->getLocaleForFirstDomain());

        return [
            'mainImage' => [
                'url' => $this->getBaseUrlPath('/content-test/images/transportGroup/' . $imageId . '.png'),
                'name' => $name,
            ],
            'name' => $name,
        ];
    }

    public function testTransportsAreOrderedAndFlaggedWhenCartRequiresPersonalPickup(): void
    {
        $this->setProductAsPersonalPickupOnly(ProductDataFixture::PRODUCT_PREFIX . 1);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsAvailabilityForCartQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        $personalPickupOnlyProductFullName = $this->getProductFullNameForFirstDomain(ProductDataFixture::PRODUCT_PREFIX . 1);
        $hasSeenUnavailableTransport = false;

        foreach ($responseData as $transport) {
            if ($transport['isPersonalPickup']) {
                $this->assertSame([], $transport['productsBlockingSelectionInCart']);
                $this->assertFalse(
                    $hasSeenUnavailableTransport,
                    'Personal pickup transports must be listed before the unavailable ones',
                );
            } else {
                $this->assertSame(
                    [['fullName' => $personalPickupOnlyProductFullName]],
                    $this->getBlockingProductsByReason($transport)[TransportUnavailabilityReasonInCartEnum::PERSONAL_PICKUP_REQUIRED],
                );
                $hasSeenUnavailableTransport = true;
            }
        }

        $this->assertTrue(
            $hasSeenUnavailableTransport,
            'At least one non-personal-pickup transport is expected to be flagged as unavailable',
        );
    }

    public function testTransportExcludedForProductIsFlaggedButStillReturned(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsAvailabilityForCartQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        $droneName = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $excludingProductFullName = $this->getProductFullNameForFirstDomain(ProductDataFixture::PRODUCT_PREFIX . 1);
        $transportsWithExcludedProducts = [];

        foreach ($responseData as $transport) {
            $blockingProductsByReason = $this->getBlockingProductsByReason($transport);

            if (array_key_exists(TransportUnavailabilityReasonInCartEnum::EXCLUDED_FOR_PRODUCT, $blockingProductsByReason)) {
                $transportsWithExcludedProducts[$transport['name']] = $blockingProductsByReason[TransportUnavailabilityReasonInCartEnum::EXCLUDED_FOR_PRODUCT];
            }
        }

        $this->assertArrayHasKey($droneName, $transportsWithExcludedProducts);
        $this->assertSame([['fullName' => $excludingProductFullName]], $transportsWithExcludedProducts[$droneName]);
    }

    public function testTransportBlockedByBothReasonsListsAllProductsThatCannotBeDelivered(): void
    {
        $this->setProductAsPersonalPickupOnly(ProductDataFixture::PRODUCT_PREFIX . 72);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/TransportsAvailabilityForCartQuery.graphql', [
            'cartUuid' => CartDataFixture::CART_UUID,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'transports');

        $transportsByName = [];

        foreach ($responseData as $transport) {
            $transportsByName[$transport['name']] = $transport;
        }

        $droneName = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $commonTransportName = t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $excludingProductFullName = $this->getProductFullNameForFirstDomain(ProductDataFixture::PRODUCT_PREFIX . 1);
        $personalPickupOnlyProductFullName = $this->getProductFullNameForFirstDomain(ProductDataFixture::PRODUCT_PREFIX . 72);

        $this->assertSame(
            [
                [
                    'reason' => TransportUnavailabilityReasonInCartEnum::EXCLUDED_FOR_PRODUCT,
                    'products' => [['fullName' => $excludingProductFullName]],
                ],
                [
                    'reason' => TransportUnavailabilityReasonInCartEnum::PERSONAL_PICKUP_REQUIRED,
                    'products' => [['fullName' => $personalPickupOnlyProductFullName]],
                ],
            ],
            $transportsByName[$droneName]['productsBlockingSelectionInCart'],
        );

        $this->assertSame(
            [
                [
                    'reason' => TransportUnavailabilityReasonInCartEnum::PERSONAL_PICKUP_REQUIRED,
                    'products' => [['fullName' => $personalPickupOnlyProductFullName]],
                ],
            ],
            $transportsByName[$commonTransportName]['productsBlockingSelectionInCart'],
        );
    }

    /**
     * @param array{productsBlockingSelectionInCart: array<int, array{reason: string, products: array<int, array{fullName: string}>}>} $transport
     * @return array<string, array<int, array{fullName: string}>>
     */
    private function getBlockingProductsByReason(array $transport): array
    {
        $blockingProductsByReason = [];

        foreach ($transport['productsBlockingSelectionInCart'] as $productsGroup) {
            $blockingProductsByReason[$productsGroup['reason']] = $productsGroup['products'];
        }

        return $blockingProductsByReason;
    }

    private function getProductFullNameForFirstDomain(string $productReferenceName): string
    {
        $product = $this->getReference($productReferenceName, Product::class);

        return $product->getFullName($this->getFirstDomainLocale());
    }

    private function setProductAsPersonalPickupOnly(string $productReferenceName): void
    {
        $product = $this->getReference($productReferenceName, Product::class);

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->personalPickupOnly = true;
        $this->productFacade->edit($product->getId(), $productData);
    }
}
