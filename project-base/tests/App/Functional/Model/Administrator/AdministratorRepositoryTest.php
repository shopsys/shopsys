<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Administrator;

use App\DataFixtures\Demo\AdministratorDataFixture;
use DateTime;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AdministratorRepositoryTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     * @inject
     */
    private $administratorRepository;

    public function testGetByValidMultidomainLogin()
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('+60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration($validMultidomainLoginToken, $multidomainLoginTokenExpiration);
        $this->getEntityManager()->flush($administrator);

        $administratorFromDb = $this->administratorRepository->getByValidMultidomainLoginToken($validMultidomainLoginToken);

        $this->assertSame($administrator, $administratorFromDb);
    }

    public function testGetByValidMultidomainLoginTokenInvalidTokenException()
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $invalidMultidomainLoginToken = 'invalidMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('+60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration($validMultidomainLoginToken, $multidomainLoginTokenExpiration);
        $this->getEntityManager()->flush($administrator);

        $this->expectException('\Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException');

        $this->administratorRepository->getByValidMultidomainLoginToken($invalidMultidomainLoginToken);
    }

    public function testGetByValidMultidomainLoginTokenExpiredTokenException()
    {
        $validMultidomainLoginToken = 'validMultidomainLoginToken';
        $multidomainLoginTokenExpiration = new DateTime('-60 seconds');

        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getReference(AdministratorDataFixture::ADMINISTRATOR);

        $administrator->setMultidomainLoginTokenWithExpiration($validMultidomainLoginToken, $multidomainLoginTokenExpiration);
        $this->getEntityManager()->flush($administrator);

        $this->expectException('\Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException');

        $this->administratorRepository->getByValidMultidomainLoginToken($validMultidomainLoginToken);
    }
}
