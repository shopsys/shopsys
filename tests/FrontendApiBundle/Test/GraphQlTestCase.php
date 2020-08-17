<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\FunctionalTestCase;

abstract class GraphQlTestCase extends FunctionalTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @var string
     */
    protected $overwriteDomainUrl;

    protected function setUp(): void
    {
        $this->client = $this->findClient(true);

        /*
         * Newly created client has its own container
         * To be able to isolate requests made with this new client,
         * it's necessary to start transaction on entityManager from the client's container.
         */

        $this->domain = $this->client->getContainer()->get(Domain::class);
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->overwriteDomainUrl = $this->getContainer()->getParameter('overwrite_domain_url');

        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $this->runCheckTestEnabledOnCurrentDomain();

        $this->em->beginTransaction();

        parent::setUp();
    }

    protected function runCheckTestEnabledOnCurrentDomain(): void
    {
        $enabledOnCurrentDomainChecker = $this->getContainer()->get(EnabledOnDomainChecker::class);

        if (!$enabledOnCurrentDomainChecker->isEnabledOnCurrentDomain()) {
            $this->markTestSkipped('Frontend API disabled on domain');
        }
    }

    protected function tearDown(): void
    {
        $this->em->rollback();
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

    /**
     * @param string $uri
     * @return string
     */
    protected function getFullUrlPath(string $uri): string
    {
        return $this->overwriteDomainUrl . $uri;
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getErrorsFromResponse(array $response): array
    {
        return $response['errors'];
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getErrorsExtensionValidationFromResponse(array $response): array
    {
        return $this->getErrorsFromResponse($response)[0]['extensions']['validation'];
    }

    /**
     * @param array $response
     * @param string $graphQlType
     * @return array
     */
    protected function getResponseDataForGraphQlType(array $response, string $graphQlType): array
    {
        return $response['data'][$graphQlType];
    }

    /**
     * @param array $response
     * @param string $graphQlType
     */
    protected function assertResponseContainsArrayOfDataForGraphQlType(array $response, string $graphQlType): void
    {
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey($graphQlType, $response['data']);
        $this->assertIsArray($response['data'][$graphQlType]);
    }

    /**
     * @param array $response
     */
    protected function assertResponseContainsArrayOfErrors(array $response): void
    {
        $this->assertArrayHasKey('errors', $response);
        $this->assertIsArray($response['errors']);
    }

    /**
     * @param array $response
     */
    protected function assertResponseContainsArrayOfExtensionValidationErrors(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('extensions', $errors[0]);
        $this->assertArrayHasKey('validation', $errors[0]['extensions']);
        $this->assertIsArray($errors[0]['extensions']['validation']);
    }
}
