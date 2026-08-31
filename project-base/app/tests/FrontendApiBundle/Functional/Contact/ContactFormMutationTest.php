<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Contact;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ContactFormMutationTest extends GraphQlTestCase
{
    private const int CONTACT_FORM_RATE_LIMIT = 3;

    public function testValidMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => 'email@example.com',
            'message' => 'Thanks',
        ]);

        self::assertTrue($response['data']['ContactForm'] ?? null);
    }

    public function testFilledHoneyPotIsSilentlyDiscarded(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => 'not-an-email',
            'message' => 'Thanks',
            'subject' => 'Cheap pills, buy now',
        ]);

        self::assertArrayNotHasKey('errors', $response);
        self::assertTrue($response['data']['ContactForm'] ?? null);
    }

    public function testDisallowEmptyEmailMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => '',
            'message' => 'Thanks',
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowInvalidEmailMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => 'email',
            'message' => 'Thanks',
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(Email::INVALID_FORMAT_ERROR, $violationCode);
    }

    public function testDisallowEmptyNameMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => '  ',
            'email' => 'email@example.com',
            'message' => 'Thanks',
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.name'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowEmptyMessageMutation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => 'email@example.com',
            'message' => '  ',
        ]);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.message'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testRateLimitIsAppliedToRepeatedSubmissions(): void
    {
        for ($attempt = 0; $attempt < self::CONTACT_FORM_RATE_LIMIT; $attempt++) {
            self::assertTrue($this->sendContactForm()['data']['ContactForm'] ?? null);
        }

        $this->assertUserError($this->sendContactForm(), 'too-many-form-submissions');
    }

    private function sendContactForm(): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/ContactFormMutation.graphql', [
            'name' => 'Name',
            'email' => 'email@example.com',
            'message' => 'Thanks',
        ]);
    }
}
