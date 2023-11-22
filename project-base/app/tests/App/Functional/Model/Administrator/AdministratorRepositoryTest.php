<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator;

use App\DataFixtures\Demo\AdministratorDataFixture;
use DateTime;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Tests\App\Test\TransactionFunctionalTestCase;

class AdministratorRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private AdministratorRepository $administratorRepository;

    public function testGetByValidMultidomainLogin(): void
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('+60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration(
            $validMultidomainLoginToken,
            $multidomainLoginTokenExpiration,
        );
        $this->em->flush();

        $administratorFromDb = $this->administratorRepository->getByValidMultidomainLoginToken(
            $validMultidomainLoginToken,
        );

        $this->assertSame($administrator, $administratorFromDb);
    }

    public function testGetByValidMultidomainLoginTokenInvalidTokenException(): void
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $invalidMultidomainLoginToken = 'invalidMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('+60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration(
            $validMultidomainLoginToken,
            $multidomainLoginTokenExpiration,
        );
        $this->em->flush();

        $this->expectException(InvalidTokenException::class);

        $this->administratorRepository->getByValidMultidomainLoginToken($invalidMultidomainLoginToken);
    }

    public function testGetByValidMultidomainLoginTokenExpiredTokenException(): void
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('-60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration(
            $validMultidomainLoginToken,
            $multidomainLoginTokenExpiration,
        );
        $this->em->flush();

        $this->expectException(InvalidTokenException::class);

        $this->administratorRepository->getByValidMultidomainLoginToken($validMultidomainLoginToken);
    }
}
