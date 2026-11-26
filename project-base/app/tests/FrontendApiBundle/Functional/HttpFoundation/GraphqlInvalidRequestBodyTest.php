<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\HttpFoundation;

use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\Response;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GraphqlInvalidRequestBodyTest extends GraphQlTestCase
{
    public function testRequestWithInvalidJsonBodyIsRejectedAsBadRequest(): void
    {
        $path = $this->getLocalizedPathOnFirstDomainByRouteName('overblog_graphql_endpoint');

        self::$client->request(
            'POST',
            $path,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: "\x1f\x8b\x08\x00\xff\xfe not a json",
        );

        $response = Json::decode((string)self::$client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_BAD_REQUEST, self::$client->getResponse()->getStatusCode());
        $this->assertSame('POST body sent invalid JSON', $response['errors'][0]['message']);
    }
}
