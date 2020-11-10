<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

abstract class GraphQlWithLoginTestCase extends GraphQlTestCase
{
    public const DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';
    public const DEFAULT_USER_PASSWORD = 'user123';

    /**
     * @var string
     */
    protected $accessToken;

    protected function setUp(): void
    {
        $this->client = $this->findClient(true);
        $this->domain = $this->client->getContainer()->get(Domain::class);
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);
        $firstDomain = $this->domain->getCurrentDomainConfig();
        $this->firstDomainUrl = $firstDomain->getUrl();

        $this->runCheckTestEnabledOnCurrentDomain();

        $this->accessToken = $this->getAccessToken(static::DEFAULT_USER_EMAIL, static::DEFAULT_USER_PASSWORD);

        parent::setUp();
    }

    /**
     * @param string $query
     * @param string $jsonExpected
     * @param string $jsonVariables
     */
    protected function assertQueryWithExpectedJson(string $query, string $jsonExpected, $jsonVariables = '{}'): void
    {
        $this->assertQueryWithExpectedArray(
            $query,
            json_decode($jsonExpected, true),
            json_decode($jsonVariables, true)
        );
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
        $server = array_merge(
            ['CONTENT_TYPE' => 'application/graphql', 'HTTP_Authorization' => sprintf(
                'Bearer %s',
                $this->accessToken
            )],
            $customServer
        );

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
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return string
     */
    private function getAccessToken(?string $customerUserEmail = null, ?string $customerUserPassword = null): string
    {
        $responseData = parent::getResponseContentForQuery(
            $this->getLoginQuery($customerUserEmail, $customerUserPassword)
        );

        return $responseData['data']['Login']['accessToken'];
    }

    /**
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return string
     */
    private static function getLoginQuery(?string $customerUserEmail = null, ?string $customerUserPassword = null): string
    {
        $customerUserEmail = $customerUserEmail === null ? self::DEFAULT_USER_EMAIL : $customerUserEmail;
        $customerUserPassword = $customerUserPassword === null ? self::DEFAULT_USER_PASSWORD : $customerUserPassword;

        return '
            mutation {
                Login(input: {
                    email: "' . $customerUserEmail . '"
                    password: "' . $customerUserPassword . '"
                }) {
                    accessToken
                    refreshToken
                }
            }
        ';
    }
}
