<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\MutationAntiXss;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MutationAntiXssValidationTest extends GraphQlTestCase
{
    /**
     * Test that clean input passes validation
     */
    public function testValidContactFormMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a clean message without any XSS content.',
        ]);

        self::assertTrue($response['data']['ContactForm'] ?? null);
    }

    /**
     * Test that XSS content in message field triggers validation error
     */
    public function testXssInMessageTriggersValidation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => '<script>alert("XSS attack")</script>',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        // Check that the validation error is related to dangerous content
        $this->assertArrayHasKey('input.message', $validationErrors);
        $errorMessage = $validationErrors['input.message'][0]['message'];
        $this->assertStringContainsString('potentially dangerous content', $errorMessage);
    }

    /**
     * Test that XSS content in name field triggers validation error
     */
    public function testXssInNameTriggersValidation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => '<img src=x onerror=alert("XSS")>',
            'email' => 'john@example.com',
            'message' => 'Clean message',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertArrayHasKey('input.name', $validationErrors);
        $errorMessage = $validationErrors['input.name'][0]['message'];
        $this->assertStringContainsString('potentially dangerous content', $errorMessage);
    }

    /**
     * Test that multiple XSS fields trigger multiple validation errors
     */
    public function testMultipleXssFieldsTriggersMultipleValidationErrors(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            'email' => 'john@example.com',
            'message' => '<body onload=alert("XSS")>',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        // Both name and message should have validation errors
        $this->assertArrayHasKey('input.name', $validationErrors);
        $this->assertArrayHasKey('input.message', $validationErrors);

        $this->assertStringContainsString('potentially dangerous content', $validationErrors['input.name'][0]['message']);
        $this->assertStringContainsString('potentially dangerous content', $validationErrors['input.message'][0]['message']);
    }

    /**
     * Test various XSS attack vectors are properly detected
     */
    public function testVariousXssVectorsAreDetected(): void
    {
        $xssVectors = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            '<body onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<svg onload=alert("XSS")>',
            '<input onfocus=alert("XSS") autofocus>',
            '<marquee onstart=alert("XSS")>',
        ];

        foreach ($xssVectors as $vector) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'message' => $vector,
            ]);

            $this->assertResponseContainsArrayOfErrors($response);
            $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
            $this->assertArrayHasKey('input.message', $validationErrors, "Failed to detect XSS in: {$vector}");
        }
    }

    /**
     * Test edge cases with special characters that should not trigger false positives
     */
    public function testSpecialCharactersFalsePositives(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'John & Jane <Company>',
            'email' => 'test+tag@example.com',
            'message' => 'We need info about <product name> and pricing > $100.',
        ]);

        // Should succeed as these are legitimate uses of < and > characters
        self::assertTrue($response['data']['ContactForm'] ?? null);
    }
}
