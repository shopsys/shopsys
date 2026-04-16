<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator\McpToken;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdministratorMcpTokenFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorMcpTokenFacade $administratorMcpTokenFacade;

    public function testGenerateRegenerateRevokeAndVerifyToken(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $firstIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);

        $this->assertNotNull($this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));

        $secondIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);

        $this->assertNotSame($firstIssuedToken->getTokenString(), $secondIssuedToken->getTokenString());
        $this->assertNotNull($this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));

        $this->administratorMcpTokenFacade->revokeManualTokenForAdministrator($administrator);

        $this->assertNull($this->administratorMcpTokenFacade->findActiveManualTokenByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondIssuedToken->getTokenString()));
    }

    public function testGenerateTokenForOneClientDoesNotRevokeTokenForAnotherClient(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $manualIssuedToken = $this->administratorMcpTokenFacade->issueManualTokenForAdministrator($administrator);
        $connectedClientId = 'connected-client-id';
        $connectedClientName = 'Connected client';
        $clientIssuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            $connectedClientId,
            $connectedClientName,
        );

        $manualToken = $this->administratorMcpTokenFacade->findActiveByAdministratorAndClient(
            $administrator,
            AdministratorMcpToken::MANUAL_CLIENT_ID,
        );
        $clientToken = $this->administratorMcpTokenFacade->findActiveByAdministratorAndClient(
            $administrator,
            $connectedClientId,
        );

        $this->assertNotNull($manualToken);
        $this->assertSame(AdministratorMcpToken::MANUAL_CLIENT_ID, $manualToken->getClientId());
        $this->assertSame(AdministratorMcpToken::MANUAL_CLIENT_NAME, $manualToken->getClientName());
        $this->assertNotNull($clientToken);
        $this->assertSame($connectedClientId, $clientToken->getClientId());
        $this->assertSame($connectedClientName, $clientToken->getClientName());
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($manualIssuedToken->getTokenString()));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($clientIssuedToken->getTokenString()));
    }
}
