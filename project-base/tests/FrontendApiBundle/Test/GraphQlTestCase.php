<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\FunctionalTestCase;

abstract class GraphQlTestCase extends FunctionalTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->getClient(true);

        $enabledOnCurrentDomainChecker = $this->getContainer()->get(EnabledOnDomainChecker::class);

        if (!$enabledOnCurrentDomainChecker->isEnabledOnCurrentDomain()) {
            $this->markTestSkipped('Frontend API disabled on domain');
        }

        parent::setUp();
    }

    /**
     * @param string $query
     * @param string $jsonExpected
     * @param string $jsonVariables
     */
    protected function assertQueryWithExpectedJson(string $query, string $jsonExpected, $jsonVariables = '{}'): void
    {
        $this->assertQueryWithExpectedArray($query, json_decode($jsonExpected, true), json_decode($jsonVariables, true));
    }

    /**
     * @param string $query
     * @param array $expected
     * @param array $variables
     */
    protected function assertQueryWithExpectedArray(string $query, array $expected, array $variables = []): void
    {
        $response = $this->getResponseForQuery($query, $variables);

        $this->assertSame(200, $response->getStatusCode());

        $result = $response->getContent();
        $this->assertEquals($expected, json_decode($result, true), $result);
    }

    /**
     * @param string $query
     * @param array $variables
     * @return array
     */
    protected function getResponseContentForQuery(string $query, array $variables = []): array
    {
        $content = $this->getResponseForQuery($query, $variables)->getContent();

        return json_decode($content, true);
    }

    /**
     * @param string $query
     * @param array $variables
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    private function getResponseForQuery(string $query, array $variables): ?Response
    {
        $path = $this->getContainer()->get('router')->generate('overblog_graphql_endpoint');

        $this->client->request(
            'GET',
            $path,
            ['query' => $query, 'variables' => json_encode($variables)],
            [],
            ['CONTENT_TYPE' => 'application/graphql']
        );

        return $this->client->getResponse();
    }
}
