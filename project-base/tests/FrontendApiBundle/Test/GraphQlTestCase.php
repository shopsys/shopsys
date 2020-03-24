<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\FunctionalTestCase;

abstract class GraphQlTestCase extends FunctionalTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker
     * @inject
     */
    protected $enabledOnCurrentDomainChecker;

    protected function setUp(): void
    {
        $this->client = $this->findClient(true);

        if (!$this->enabledOnCurrentDomainChecker->isEnabledOnCurrentDomain()) {
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
     * @param array $customServer
     * @return array
     */
    protected function getResponseContentForQuery(string $query, array $variables = [], array $customServer = []): array
    {
        $content = $this->getResponseForQuery($query, $variables, $customServer)->getContent();

        return json_decode($content, true);
    }

    /**
     * @param string $query
     * @param array $variables
     * @param array $customServer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getResponseForQuery(string $query, array $variables, array $customServer = []): Response
    {
        $path = $this->getLocalizedPathOnFirstDomainByRouteName('overblog_graphql_endpoint');
        $server = array_merge(['CONTENT_TYPE' => 'application/graphql'], $customServer);

        $this->client->request(
            'GET',
            $path,
            ['query' => $query, 'variables' => json_encode($variables)],
            [],
            $server
        );

        return $this->client->getResponse();
    }

    /**
     * @return string
     */
    protected function getLocaleForFirstDomain(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
