<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Inquiry;

use App\DataFixtures\Demo\ProductDataFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CreateInquiryTest extends GraphQlTestCase
{
    #[DataProvider('createInquiryDataProvider')]
    public function testCreateInquiry(
        array $telephone,
        string $firstName,
        string $lastName,
        string $email,
        ?string $companyName = null,
        ?string $companyNumber = null,
        ?string $companyTaxNumber = null,
        ?string $note = null,
    ): void {
        $productUuid = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '45')->getUuid();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateInquiryMutation.graphql',
            [
                'telephone' => $telephone,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'companyName' => $companyName,
                'companyNumber' => $companyNumber,
                'companyTaxNumber' => $companyTaxNumber,
                'note' => $note,
                'productUuid' => $productUuid,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'CreateInquiry');

        $this->assertTrue($data);
    }

    #[DataProvider('createInquiryDataProvider')]
    public function testInvalidProductUuid(
        array $telephone,
        string $firstName,
        string $lastName,
        string $email,
        ?string $companyName = null,
        ?string $companyNumber = null,
        ?string $companyTaxNumber = null,
        ?string $note = null,
    ): void {
        $notExistingProductUuid = '00000000-0000-0000-0000-000000000001';

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateInquiryMutation.graphql',
            [
                'telephone' => $telephone,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'companyName' => $companyName,
                'companyNumber' => $companyNumber,
                'companyTaxNumber' => $companyTaxNumber,
                'note' => $note,
                'productUuid' => $notExistingProductUuid,
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
    }

    public static function createInquiryDataProvider(): iterable
    {
        yield [
            'telephone' => [
                'countryCode' => 'CZ',
                'prefix' => '+420',
                'number' => '123456789',
            ],
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email@example.com',
        ];

        yield [
            'telephone' => [
                'countryCode' => 'CZ',
                'prefix' => '+420',
                'number' => '123456789',
            ],
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email@example.com',
            'companyName' => 'companyName',
            'companyNumber' => '1234567',
            'companyTaxNumber' => 'EN65432101',
        ];
    }
}
