<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Inquiry;

use App\DataFixtures\Demo\ProductDataFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CreateInquiryTest extends GraphQlTestCase
{
    /**
     * @param string $telephone
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string|null $companyName
     * @param string|null $companyNumber
     * @param string|null $companyTaxNumber
     * @param string|null $note
     */
    #[DataProvider('createInquiryDataProvider')]
    public function testCreateInquiry(
        string $telephone,
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

    /**
     * @param string $telephone
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string|null $companyName
     * @param string|null $companyNumber
     * @param string|null $companyTaxNumber
     * @param string|null $note
     */
    #[DataProvider('createInquiryDataProvider')]
    public function testInvalidProductUuid(
        string $telephone,
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

    /**
     * @return iterable
     */
    public static function createInquiryDataProvider(): iterable
    {
        yield [
            'telephone' => '+53123456789',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email@example.com',
        ];

        yield [
            'telephone' => '+53123456789',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email@example.com',
            'companyName' => 'companyName',
            'companyNumber' => '1234567',
            'companyTaxNumber' => 'EN65432101',
        ];
    }
}
