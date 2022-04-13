<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\FrontendApi\Model\Component\Constraints\ExistingEmail;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RequestPasswordRecoveryTest extends GraphQlTestCase
{
    public function testRequestPasswordRecoveryForExistingUser(): void
    {
        $query = '
            mutation {
                RequestPasswordRecovery(email: "no-reply@shopsys.com")
            }';

        $jsonExpected = '
            {
                "data": {
                    "RequestPasswordRecovery": "success"
                }
            }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testRequestPasswordRecoveryForNotExistingUser(): void
    {
        $query = '
            mutation {
                RequestPasswordRecovery(email: "does-not-exist@shopsys.com")
            }';

        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(ExistingEmail::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR, $validationErrors['email'][0]['code']);
    }
}
