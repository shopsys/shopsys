<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Logout;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class LogoutTest extends GraphQlWithLoginTestCase
{
    public function testLogoutMutation()
    {
        $response = $this->getResponseContentForQuery($this->getLogoutQuery());
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('Logout', $response['data']);
        $isLogoutSuccess = $response['data']['Logout'];
        $this->assertTrue($isLogoutSuccess);
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
