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
    protected AdministratorMcpTokenFacade $administratorMcpTokenFacade;

    public function testGenerateRegenerateRevokeAndVerifyToken(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $firstTokenString = $this->administratorMcpTokenFacade->generateTokenForAdministrator($administrator);

        $this->assertNotNull($this->administratorMcpTokenFacade->findActiveByAdministrator($administrator));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstTokenString));

        $secondTokenString = $this->administratorMcpTokenFacade->generateTokenForAdministrator($administrator);

        $this->assertNotSame($firstTokenString, $secondTokenString);
        $this->assertNotNull($this->administratorMcpTokenFacade->findActiveByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($firstTokenString));
        $this->assertNotNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondTokenString));

        $this->administratorMcpTokenFacade->revokeTokenForAdministrator($administrator);

        $this->assertNull($this->administratorMcpTokenFacade->findActiveByAdministrator($administrator));
        $this->assertNull($this->administratorMcpTokenFacade->findValidTokenByTokenString($secondTokenString));
    }
}
