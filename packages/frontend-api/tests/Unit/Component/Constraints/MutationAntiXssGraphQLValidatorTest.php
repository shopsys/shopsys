<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\Constraints;

use Override;
use Shopsys\FrontendApiBundle\Component\Constraints\MutationAntiXss;
use Shopsys\FrontendApiBundle\Component\Constraints\MutationAntiXssValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MutationAntiXssGraphQLValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @return \Shopsys\FrontendApiBundle\Component\Constraints\MutationAntiXssValidator
     */
    #[Override]
    protected function createValidator(): MutationAntiXssValidator
    {
        return new MutationAntiXssValidator();
    }

    public function testValidationOfCleanData(): void
    {
        $constraint = new MutationAntiXss([
            'excludedFields' => [],
        ]);

        $this->validator->validate([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a clean message',
        ], $constraint);

        $this->assertNoViolation();
    }

    public function testValidationOfXssData(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => []]);

        $this->validator->validate([
            'name' => 'John Doe',
            'message' => '<script>alert("XSS")</script>',
        ], $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.message')
            ->setCode($constraint::ERROR_CODE)
            ->assertRaised();
    }

    public function testValidationWithExcludedFields(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => ['sessionToken']]);

        $this->validator->validate([
            'sessionToken' => '<script>alert("XSS")</script>', // Should be excluded
            'message' => 'Clean message',
        ], $constraint);

        $this->assertNoViolation();
    }

    public function testValidationOfNestedData(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => []]);

        $this->validator->validate([
            'user' => [
                'name' => 'John Doe',
                'bio' => '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            ],
        ], $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.user.bio')
            ->setCode($constraint::ERROR_CODE)
            ->assertRaised();
    }

    public function testValidationWithAutoExcludedFields(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => []]);

        $this->validator->validate([
            'userId' => '<script>alert("XSS")</script>', // Should be auto-excluded (ends with Id)
            'orderUuid' => '<img src=x onerror=alert("XSS")>', // Should be auto-excluded (ends with Uuid)
            'accessToken' => 'javascript:alert("XSS")', // Should be auto-excluded (ends with Token)
            'promoCode' => '<iframe src="evil.com"></iframe>', // Should be auto-excluded (ends with Code)
            'message' => 'Clean message',
        ], $constraint);

        $this->assertNoViolation();
    }

    public function testValidationOfComplexMutationData(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => []]);

        $this->validator->validate([
            'input' => [
                'customerData' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john@example.com',
                ],
                'orderData' => [
                    'items' => [
                        [
                            'productUuid' => '123e4567-e89b-12d3-a456-426614174000', // Auto-excluded
                            'quantity' => 2,
                            'note' => '<script>alert("XSS in note")</script>', // Should trigger violation
                        ],
                    ],
                    'deliveryAddress' => [
                        'street' => '123 Main St',
                        'note' => 'Clean delivery note',
                    ],
                ],
            ],
        ], $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.input.orderData.items.0.note')
            ->setCode($constraint::ERROR_CODE)
            ->assertRaised();
    }

    public function testValidationOfStringValue(): void
    {
        $constraint = new MutationAntiXss(['excludedFields' => []]);

        $this->validator->validate('<body onload=alert("XSS")>', $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path')
            ->setCode($constraint::ERROR_CODE)
            ->assertRaised();
    }
}
