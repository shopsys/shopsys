<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ProductQuestion;

use App\DataFixtures\Demo\ProductDataFixture;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductQuestionMutationTest extends GraphQlTestCase
{
    public function testValidMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => 'John Doe',
            'email' => 'email@example.com',
            'question' => 'Is this product available in blue?',
            'productUuid' => $this->getProductUuid(),
        ]);

        self::assertTrue($this->getResponseDataForGraphQlType($response, 'ProductQuestion'));
    }

    public function testDisallowEmptyNameMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => '  ',
            'email' => 'email@example.com',
            'question' => 'Question',
            'productUuid' => $this->getProductUuid(),
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.customerName'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowEmptyEmailMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => 'John Doe',
            'email' => '',
            'question' => 'Question',
            'productUuid' => $this->getProductUuid(),
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowInvalidEmailMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => 'John Doe',
            'email' => 'email',
            'question' => 'Question',
            'productUuid' => $this->getProductUuid(),
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(Email::INVALID_FORMAT_ERROR, $violationCode);
    }

    public function testDisallowEmptyQuestionMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => 'John Doe',
            'email' => 'email@example.com',
            'question' => '  ',
            'productUuid' => $this->getProductUuid(),
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.question'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testInvalidProductUuidMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuestionMutation.graphql', [
            'customerName' => 'John Doe',
            'email' => 'email@example.com',
            'question' => 'Question',
            'productUuid' => '00000000-0000-0000-0000-000000000001',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
    }

    private function getProductUuid(): string
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1')->getUuid();
    }
}
