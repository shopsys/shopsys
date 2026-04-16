<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Tests\App\Test\Client;
use Tests\App\Test\TransactionFunctionalTestCase;

final class McpOAuthAuthorizationControllerTest extends TransactionFunctionalTestCase
{
    private const string ADMIN_IP_ADDRESS = '127.0.0.1';

    /**
     * @inject
     */
    private AdministratorMcpTokenFacade $administratorMcpTokenFacade;

    public function testAuthorizeOauthClientAndExchangeToken(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);
        $authorizePath = $this->generatePath($client, 'admin_superadmin_mcp_oauth_authorize');
        $tokenPath = $this->generatePath($client, 'mcp_oauth_token');
        $registerPath = $this->generatePath($client, 'mcp_oauth_register');
        $redirectUri = 'http://127.0.0.1:8765/callback';
        $registration = $this->registerClient($client, $registerPath, $redirectUri, 'Claude Code');
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $codeVerifier = 'shopsys-mcp-code-verifier';
        $codeChallenge = $this->createPkceCodeChallenge($codeVerifier);

        $this->logInAdministrator($client, $superadministrator->getId());
        $crawler = $client->request('GET', $authorizePath, [
            'response_type' => 'code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $redirectUri,
            'state' => 'oauth-state',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $csrfToken = $crawler->filter('input[name="mcp_oauth_authorization_form[_token]"]')->attr('value');
        $client->request('POST', $authorizePath, [
            'mcp_oauth_authorization_form' => [
                'clientId' => $registration['client_id'],
                'codeChallenge' => $codeChallenge,
                'redirectUri' => $redirectUri,
                'state' => 'oauth-state',
                '_token' => $csrfToken,
                'approve' => '',
            ],
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseRedirects();
        $redirectLocation = (string)$client->getResponse()->headers->get('Location');
        $redirectQueryParameters = [];
        parse_str((string)parse_url($redirectLocation, PHP_URL_QUERY), $redirectQueryParameters);

        $this->assertSame('oauth-state', $redirectQueryParameters['state'] ?? null);
        $this->assertArrayHasKey('code', $redirectQueryParameters);

        $client->request('POST', $tokenPath, [
            'grant_type' => 'authorization_code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $redirectUri,
            'code' => $redirectQueryParameters['code'],
            'code_verifier' => $codeVerifier,
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseStatusCodeSame(200);

        /** @var array{access_token: string, token_type: string, expires_in: int} $tokenResponseData */
        $tokenResponseData = $client->getResponseData();

        $this->assertSame('Bearer', $tokenResponseData['token_type']);
        $this->assertGreaterThan(0, $tokenResponseData['expires_in']);

        $issuedToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($tokenResponseData['access_token']);

        $this->assertNotNull($issuedToken);
        $this->assertSame($registration['client_id'], $issuedToken->getClientId());
        $this->assertSame($registration['client_name'], $issuedToken->getClientName());
    }

    public function testDenyOauthClientAuthorization(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);
        $authorizePath = $this->generatePath($client, 'admin_superadmin_mcp_oauth_authorize');
        $registerPath = $this->generatePath($client, 'mcp_oauth_register');
        $redirectUri = 'http://127.0.0.1:8765/callback';
        $registration = $this->registerClient($client, $registerPath, $redirectUri, 'Claude Code');
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $codeVerifier = 'shopsys-mcp-code-verifier';
        $codeChallenge = $this->createPkceCodeChallenge($codeVerifier);

        $this->logInAdministrator($client, $superadministrator->getId());
        $crawler = $client->request('GET', $authorizePath, [
            'response_type' => 'code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $redirectUri,
            'state' => 'oauth-state',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $csrfToken = $crawler->filter('input[name="mcp_oauth_authorization_form[_token]"]')->attr('value');
        $client->request('POST', $authorizePath, [
            'mcp_oauth_authorization_form' => [
                'clientId' => $registration['client_id'],
                'codeChallenge' => $codeChallenge,
                'redirectUri' => $redirectUri,
                'state' => 'oauth-state',
                '_token' => $csrfToken,
                'deny' => '',
            ],
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseRedirects();

        $redirectLocation = (string)$client->getResponse()->headers->get('Location');
        $redirectQueryParameters = [];
        parse_str((string)parse_url($redirectLocation, PHP_URL_QUERY), $redirectQueryParameters);

        $this->assertSame('oauth-state', $redirectQueryParameters['state'] ?? null);
        $this->assertSame('access_denied', $redirectQueryParameters['error'] ?? null);
        $this->assertArrayNotHasKey('code', $redirectQueryParameters);
    }

    public function testAuthorizationFailsForMismatchedRedirectUri(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);
        $authorizePath = $this->generatePath($client, 'admin_superadmin_mcp_oauth_authorize');
        $registerPath = $this->generatePath($client, 'mcp_oauth_register');
        $registeredRedirectUri = 'http://127.0.0.1:8765/callback';
        $mismatchedRedirectUri = 'http://127.0.0.1:8765/other-callback';
        $registration = $this->registerClient($client, $registerPath, $registeredRedirectUri, 'Claude Code');
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $this->logInAdministrator($client, $superadministrator->getId());
        $client->request('GET', $authorizePath, [
            'response_type' => 'code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $mismatchedRedirectUri,
            'state' => 'oauth-state',
            'code_challenge' => $this->createPkceCodeChallenge('shopsys-mcp-code-verifier'),
            'code_challenge_method' => 'S256',
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame(
            'The OAuth client or redirect_uri is invalid.',
            (string)$client->getResponse()->getContent(),
        );
    }

    public function testAuthorizeOauthClientAndExchangeTokenForEquivalentLoopbackRedirectUris(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);
        $authorizePath = $this->generatePath($client, 'admin_superadmin_mcp_oauth_authorize');
        $tokenPath = $this->generatePath($client, 'mcp_oauth_token');
        $registerPath = $this->generatePath($client, 'mcp_oauth_register');
        $registeredRedirectUri = 'http://localhost:3118/callback';
        $authorizationRedirectUri = 'http://127.0.0.1:51582/callback';
        $tokenRedirectUri = 'http://[::1]:41234/callback';
        $registration = $this->registerClient($client, $registerPath, $registeredRedirectUri, 'Claude Code');
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $codeVerifier = 'shopsys-mcp-code-verifier';
        $codeChallenge = $this->createPkceCodeChallenge($codeVerifier);

        $this->logInAdministrator($client, $superadministrator->getId());
        $crawler = $client->request('GET', $authorizePath, [
            'response_type' => 'code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $authorizationRedirectUri,
            'state' => 'oauth-state',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $csrfToken = $crawler->filter('input[name="mcp_oauth_authorization_form[_token]"]')->attr('value');
        $client->request('POST', $authorizePath, [
            'mcp_oauth_authorization_form' => [
                'clientId' => $registration['client_id'],
                'codeChallenge' => $codeChallenge,
                'redirectUri' => $authorizationRedirectUri,
                'state' => 'oauth-state',
                '_token' => $csrfToken,
                'approve' => '',
            ],
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseRedirects();
        $redirectLocation = (string)$client->getResponse()->headers->get('Location');
        $redirectQueryParameters = [];
        parse_str((string)parse_url($redirectLocation, PHP_URL_QUERY), $redirectQueryParameters);

        $client->request('POST', $tokenPath, [
            'grant_type' => 'authorization_code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $tokenRedirectUri,
            'code' => $redirectQueryParameters['code'],
            'code_verifier' => $codeVerifier,
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testAuthorizationCodeCannotBeExchangedTwice(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);
        $authorizePath = $this->generatePath($client, 'admin_superadmin_mcp_oauth_authorize');
        $tokenPath = $this->generatePath($client, 'mcp_oauth_token');
        $registerPath = $this->generatePath($client, 'mcp_oauth_register');
        $redirectUri = 'http://127.0.0.1:8765/callback';
        $registration = $this->registerClient($client, $registerPath, $redirectUri, 'Claude Code');
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $codeVerifier = 'shopsys-mcp-code-verifier';
        $codeChallenge = $this->createPkceCodeChallenge($codeVerifier);

        $this->logInAdministrator($client, $superadministrator->getId());
        $crawler = $client->request('GET', $authorizePath, [
            'response_type' => 'code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $redirectUri,
            'state' => 'oauth-state',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $csrfToken = $crawler->filter('input[name="mcp_oauth_authorization_form[_token]"]')->attr('value');
        $client->request('POST', $authorizePath, [
            'mcp_oauth_authorization_form' => [
                'clientId' => $registration['client_id'],
                'codeChallenge' => $codeChallenge,
                'redirectUri' => $redirectUri,
                'state' => 'oauth-state',
                '_token' => $csrfToken,
                'approve' => '',
            ],
        ], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $redirectLocation = (string)$client->getResponse()->headers->get('Location');
        $redirectQueryParameters = [];
        parse_str((string)parse_url($redirectLocation, PHP_URL_QUERY), $redirectQueryParameters);

        $tokenRequestData = [
            'grant_type' => 'authorization_code',
            'client_id' => $registration['client_id'],
            'redirect_uri' => $redirectUri,
            'code' => $redirectQueryParameters['code'],
            'code_verifier' => $codeVerifier,
        ];

        $client->request('POST', $tokenPath, $tokenRequestData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', $tokenPath, $tokenRequestData, [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertResponseStatusCodeSame(400);

        /** @var array{error: string, error_description: string} $errorResponseData */
        $errorResponseData = $client->getResponseData();

        $this->assertSame('invalid_grant', $errorResponseData['error']);
        $this->assertSame('The authorization code is invalid or expired.', $errorResponseData['error_description']);
    }

    /**
     * @return array{client_id: string, client_name: string, redirect_uris: array<string>}
     */
    private function registerClient(
        Client $client,
        string $registerPath,
        string $redirectUri,
        string $clientName,
    ): array {
        $client->post($registerPath, [
            'redirect_uris' => [$redirectUri],
            'client_name' => $clientName,
        ]);

        $this->assertResponseStatusCodeSame(201);

        /** @var array{client_id: string, client_name: string, redirect_uris: array<string>} $registration */
        $registration = $client->getResponseData();

        return $registration;
    }

    private function logInAdministrator(Client $client, int $administratorId): void
    {
        $administrator = $client->getContainer()->get(AdministratorFacade::class)->getById($administratorId);
        $client->getContainer()->get(AdministratorActivityFacade::class)->create(
            $administrator,
            self::ADMIN_IP_ADDRESS,
        );
        $client->loginUser($administrator, 'administration');
    }

    private function createPkceCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    private function generatePath(Client $client, string $routeName): string
    {
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $client->getContainer()->get('router');

        return $router->generate($routeName);
    }
}
