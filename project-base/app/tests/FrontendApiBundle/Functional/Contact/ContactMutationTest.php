<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Contact;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ContactMutationTest extends GraphQlTestCase
{
    public function testValidMutation(): void
    {
        $mutation = 'mutation {
            Contact(input: {
                name: "Name", 
                email: "email@example.com", 
                message: "Thanks"
            })
        }';

        $response = $this->getResponseContentForQuery($mutation);
        self::assertTrue($response['data']['Contact'] ?? null);
    }

    public function testDisallowEmptyEmailMutation(): void
    {
        $mutation = 'mutation {
            Contact(input: {
                name: "Name", 
                email: "", 
                message: "Thanks"
            })
        }';

        $response = $this->getResponseContentForQuery($mutation);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowInvalidEmailMutation(): void
    {
        $mutation = 'mutation {
            Contact(input: {
                name: "Name", 
                email: "email", 
                message: "Thanks"
            })
        }';

        $response = $this->getResponseContentForQuery($mutation);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.email'][0]['code'];
        self::assertEquals(Email::INVALID_FORMAT_ERROR, $violationCode);
    }

    public function testDisallowEmptyNameMutation(): void
    {
        $mutation = 'mutation {
            Contact(input: {
                name: "  ", 
                email: "email@example.com", 
                message: "Thanks"
            })
        }';

        $response = $this->getResponseContentForQuery($mutation);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.name'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }

    public function testDisallowEmptyMessageMutation(): void
    {
        $mutation = 'mutation {
            Contact(input: {
                name: "Name", 
                email: "email@example.com", 
                message: "  "
            })
        }';

        $response = $this->getResponseContentForQuery($mutation);

        $violationCode = $this->getErrorsExtensionValidationFromResponse($response)['input.message'][0]['code'];
        self::assertEquals(NotBlank::IS_BLANK_ERROR, $violationCode);
    }
}
