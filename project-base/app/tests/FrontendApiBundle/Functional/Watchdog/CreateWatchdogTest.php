<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Watchdog;

use App\DataFixtures\Demo\ProductDataFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CreateWatchdogTest extends GraphQlTestCase
{
    #[DataProvider('createWatchdogDataProvider')]
    public function testCreateWatchdog(
        string $email,
        int $productId,
    ): void {
        $productUuid = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId)->getUuid();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateWatchdogMutation.graphql',
            [
                'email' => $email,
                'productUuid' => $productUuid,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'CreateWatchdog');

        $this->assertTrue($data);
    }

    #[DataProvider('createWatchdogDataProvider')]
    public function testInvalidProductUuid(
        string $email,
        int $productId,
    ): void {
        $notExistingProductUuid = '00000000-0000-0000-0000-000000000001';

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateWatchdogMutation.graphql',
            [
                'email' => $email,
                'productUuid' => $notExistingProductUuid,
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
    }

    #[DataProvider('createWatchdogDataProvider')]
    public function testMainVariantProductUuid(
        string $email,
        int $productId,
    ): void {
        $productUuid = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '82')->getUuid();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateWatchdogMutation.graphql',
            [
                'email' => $email,
                'productUuid' => $productUuid,
            ],
        );

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validation = $this->getErrorsExtensionValidationFromResponse($response)['input.productUuid.watchdog'][0];

        $this->assertSame('Watchdog is not available for product main variant.', $validation['message']);
        $this->assertSame('a59f8293-2803-4571-b307-2b4ce72d39b4', $validation['code']);
    }

    #[DataProvider('createWatchdogDataProvider')]
    public function testInquiryProductUuid(
        string $email,
        int $productId,
    ): void {
        $productUuid = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3')->getUuid();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateWatchdogMutation.graphql',
            [
                'email' => $email,
                'productUuid' => $productUuid,
            ],
        );

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validation = $this->getErrorsExtensionValidationFromResponse($response)['input.productUuid.watchdog'][0];

        $this->assertSame('Watchdog is not available for product inquiry.', $validation['message']);
        $this->assertSame('bd70a05a-6bbb-4783-b9b2-c42824fa067d', $validation['code']);
    }

    public static function createWatchdogDataProvider(): iterable
    {
        yield [
            'email' => 'email@example.com',
            'productId' => 1,
        ];
    }
}
