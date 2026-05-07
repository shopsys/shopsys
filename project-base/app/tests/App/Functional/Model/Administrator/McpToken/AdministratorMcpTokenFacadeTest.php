<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator\McpToken;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
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
        $initialActiveTokensCount = count($this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));

        $firstIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);
        $secondIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);

        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
        $this->assertCount($initialActiveTokensCount + 2, $this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));

        $firstToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString());
        $this->assertNotNull($firstToken);
        $this->assertSame($firstToken, $this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $firstToken->getId()));

        $this->administratorMcpTokenFacade->revokeToken($firstToken);

        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
        $this->assertCount($initialActiveTokensCount + 1, $this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $firstToken->getId()));

        $secondToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString());
        $this->assertNotNull($secondToken);
        $this->assertSame($secondToken, $this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $secondToken->getId()));

        $this->administratorMcpTokenFacade->revokeToken($secondToken);
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
        $this->assertCount($initialActiveTokensCount, $this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findActiveByIdAndAdministrator($administrator, $secondToken->getId()));
    }

    public function testGenerateTokenForOneClientDoesNotRevokeTokenForAnotherClient(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $initialActiveTokensCount = count($this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));
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

        $activeTokens = $this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator);
        $this->assertCount($initialActiveTokensCount + 2, $activeTokens);
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstClientIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondClientIssuedToken->getTokenString()));
    }

    public function testGenerateMultipleTokensForSameClientKeepsAllActive(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $initialActiveTokensCount = count($this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));
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
        $this->assertCount($initialActiveTokensCount + 2, $this->administratorMcpTokenFacade->findActiveTokensByAdministrator($administrator));
    }
}
