<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use Tests\App\Test\FunctionalTestCase;

class HomepageControllerTest extends FunctionalTestCase
{
    public function testHomepageHttpStatus200(): void
    {
        $client = $this->findClient();

        $client->request('GET', '/');
        $code = $client->getResponse()->getStatusCode();

        $this->assertSame(200, $code);
    }

    public function testHomepageHasBodyEnd(): void
    {
        $client = $this->findClient();

        $client->request('GET', '/');
        $content = $client->getResponse()->getContent();

        $this->assertMatchesRegularExpression('/<\/body>/ui', $content);
    }
}
