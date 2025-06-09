<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Constraints;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\Constraints\AntiXss;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AntiXssValidatorTest extends TestCase
{
    private ValidatorInterface $validator;

    #[Override]
    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testXssContentTriggersViolation(): void
    {
        $data = [
            'name' => 'John Doe',
            'message' => '<script>alert("XSS")</script>',
        ];

        $violations = $this->validator->validate($data, new AntiXss([
            'excludedFields' => [],
        ]));
        $this->assertCount(1, $violations);
        $this->assertEquals('message', $violations[0]->getPropertyPath());
        $this->assertStringContainsString('potentially dangerous content', $violations[0]->getMessage());
    }

    public function testExcludedFieldsAreNotValidated(): void
    {
        $data = [
            'password' => '<script>alert("XSS")</script>',
            'token' => '<img src=x onerror=alert("XSS")>',
            'name' => 'Clean Name',
        ];

        $violations = $this->validator->validate($data, new AntiXss(['excludedFields' => ['password', 'token']]));
        $this->assertCount(0, $violations);
    }

    public function testNestedObjectValidation(): void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'bio' => '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            ],
        ];

        $violations = $this->validator->validate($data, new AntiXss(['excludedFields' => []]));
        $this->assertCount(1, $violations);
        $this->assertEquals('user.bio', $violations[0]->getPropertyPath());
    }

    public function testPropertyLevelValidation(): void
    {
        $xssContent = '<body onload=alert("XSS")>';

        $violations = $this->validator->validate($xssContent, new AntiXss(['excludedFields' => []]));
        $this->assertCount(1, $violations);
        $this->assertEquals('', $violations[0]->getPropertyPath());
    }

    public function testNullAndEmptyValuesAreSkipped(): void
    {
        $violations = $this->validator->validate(null, new AntiXss());
        $this->assertCount(0, $violations);

        $violations = $this->validator->validate('', new AntiXss());
        $this->assertCount(0, $violations);

        $violations = $this->validator->validate([], new AntiXss());
        $this->assertCount(0, $violations);
    }

    public function testCustomExcludedFieldsConfiguration(): void
    {
        $data = [
            'customField' => '<script>alert("XSS")</script>',
            'specialData' => '<img src=x onerror=alert("XSS")>',
            'rawHtml' => '<iframe src="evil.com"></iframe>',
            'content' => 'This is clean content',
        ];

        $violations = $this->validator->validate($data, new AntiXss([
            'excludedFields' => ['customField', 'specialData', 'rawHtml'],
        ]));
        $this->assertCount(0, $violations);
    }

    public function testVariousXssAttackVectorsAreDetected(): void
    {
        $xssVectors = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<body onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<svg onload=alert("XSS")>',
            '<input onfocus=alert("XSS") autofocus>',
            '<select onfocus=alert("XSS") autofocus>',
            '<textarea onfocus=alert("XSS") autofocus>',
            '<marquee onstart=alert("XSS")>',
        ];

        foreach ($xssVectors as $vector) {
            $violations = $this->validator->validate($vector, new AntiXss(['excludedFields' => []]));
            $this->assertCount(1, $violations, "Failed to detect XSS in: {$vector}");
        }
    }

    public function testFrameworkValidatorOnlyUsesExplicitExclusions(): void
    {
        $data = [
            'userId' => '<script>alert("XSS")</script>',
            'accessToken' => '<img src=x onerror=alert("XSS")>',
        ];

        $violations = $this->validator->validate($data, new AntiXss(['excludedFields' => []]));
        $this->assertCount(2, $violations); // Both fields should trigger violations

        $paths = array_map(fn ($v) => $v->getPropertyPath(), iterator_to_array($violations));
        $this->assertContains('userId', $paths);
        $this->assertContains('accessToken', $paths);
    }

    public function testComplexNestedStructure(): void
    {
        $complexData = [
            'orders' => [
                [
                    'id' => 1,
                    'items' => [
                        [
                            'product' => 'Laptop',
                            'customization' => '<svg onload=alert("XSS")>',
                        ],
                        [
                            'product' => 'Mouse',
                            'customization' => 'Engraving: John Doe',
                        ],
                    ],
                    'shipping' => [
                        'address' => '123 Main St',
                        'instructions' => 'Leave at door',
                    ],
                ],
                [
                    'id' => 2,
                    'items' => [
                        [
                            'product' => 'Keyboard',
                            'customization' => 'Custom keycaps',
                        ],
                    ],
                    'shipping' => [
                        'address' => '456 Oak Ave',
                        'instructions' => '<iframe src="javascript:alert(\'XSS\')"></iframe>',
                    ],
                ],
            ],
        ];

        $violations = $this->validator->validate($complexData, new AntiXss(['excludedFields' => []]));

        $this->assertCount(2, $violations);

        $paths = [];

        foreach ($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        $this->assertContains('orders.0.items.0.customization', $paths);
        $this->assertContains('orders.1.shipping.instructions', $paths);
    }

    public function testConstraintProperties(): void
    {
        $constraint = new AntiXss([
            'message' => 'Custom message',
            'excludedFields' => ['field1', 'field2'],
        ]);

        $this->assertEquals('Custom message', $constraint->message);
        $this->assertEquals(['field1', 'field2'], $constraint->excludedFields);
    }
}
