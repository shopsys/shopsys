<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator\McpToken;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use DateTimeImmutable;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdministratorMcpTokenFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorMcpTokenFacade $administratorMcpTokenFacade;

    public function testGenerateMultipleManualTokensAndRevokeThemIndividually(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $firstIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);
        $secondIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);

        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));

        $firstToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString());
        $this->assertNotNull($firstToken);
        $this->assertSame($firstToken, $this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $firstToken->getId()));

        $this->administratorMcpTokenFacade->revokeToken($firstToken);

        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
        $this->assertNull($this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $firstToken->getId()));

        $secondToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString());
        $this->assertNotNull($secondToken);
        $this->assertSame($secondToken, $this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $secondToken->getId()));

        $this->administratorMcpTokenFacade->revokeToken($secondToken);
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
        $this->assertNull($this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $secondToken->getId()));
    }

    public function testGenerateTokenForOneClientDoesNotRevokeTokenForAnotherClient(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $firstClientIssuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            'connected-client-id-1',
            'Connected client 1',
        );
        $secondClientIssuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            'connected-client-id-2',
            'Connected client 2',
        );

        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstClientIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondClientIssuedToken->getTokenString()));
    }

    public function testGenerateMultipleTokensForSameClientKeepsAllActive(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $clientId = 'connected-client-id';
        $clientName = 'Connected client';

        $firstIssuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            $clientId,
            $clientName,
        );
        $secondIssuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            $clientId,
            $clientName,
        );

        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
    }

    public function testGenerateManualTokenWithCustomLabelAndExpiration(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $expiresAt = new DateTimeImmutable('+14 days');

        $issuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator(
            $administrator,
            'Codex automation',
            $expiresAt,
        );

        $token = $this->administratorMcpTokenFacade->findValidTokenByTokenString($issuedToken->getTokenString());

        $this->assertNotNull($token);
        $this->assertSame('Codex automation', $token->getLabel());
        $this->assertSame($expiresAt->getTimestamp(), $token->getExpiresAt()->getTimestamp());
        $this->assertTrue($token->isManual());
    }
}
