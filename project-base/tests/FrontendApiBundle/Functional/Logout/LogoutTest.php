<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Logout;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class LogoutTest extends GraphQlWithLoginTestCase
{
    public function testLogoutMutation()
    {
        $isLogoutSuccess = $this->getResponseContentForQuery($this->getLogoutQuery())['data']['Logout'];
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
