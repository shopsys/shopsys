<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use Tests\App\Test\ApplicationTestCase;

class HomepageControllerTest extends ApplicationTestCase
{
    public function testHomepageHttpStatus200()
    {
        $client = $this->getCurrentClient();

        $client->request('GET', '/');
        $code = $client->getResponse()->getStatusCode();

        $this->assertSame(200, $code);
    }

    public function testHomepageHasBodyEnd()
    {
        $client = $this->getCurrentClient();

        $client->request('GET', '/');
        $content = $client->getResponse()->getContent();

        $this->assertRegExp('/<\/body>/ui', $content);
    }
}
