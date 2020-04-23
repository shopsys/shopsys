<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Logout;

use Tests\FrontendApiBundle\Functional\Login\LoginTest;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LogoutTest extends GraphQlTestCase
{
    public function testLogoutMutation()
    {
        $isLogoutSuccess = $this->getResponseContentForQuery($this->getLogoutQuery(), [], ['HTTP_Authorization' => sprintf('Bearer %s', $this->getAccessToken())])['data']['Logout'];
        $this->assertTrue($isLogoutSuccess);
    }

    /**
     * @return string
     */
    private function getAccessToken(): string
    {
        $responseData = $this->getResponseContentForQuery(LoginTest::getLoginQuery())['data']['Login'];
        return $responseData['accessToken'];
    }

    /**
     * @return string
     */
    private function getLogoutQuery(): string
    {
        return '
            mutation {
                Logout
            }
        ';
    }
}
