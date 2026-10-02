<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Flag;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

final class FlagGridFactoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private FlagGridFactory $flagGridFactory;

    /**
     * @inject
     */
    private FlagRepository $flagRepository;

    /**
     * @inject
     */
    private GridOrderingFacade $gridOrderingFacade;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    public function testGridUsesSavedFlagOrderAfterReload(): void
    {
        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));
        $flagIds = array_map(static fn (Flag $flag): int => $flag->getId(), array_reverse($this->flagRepository->getAll()));

        $this->gridOrderingFacade->saveOrdering(Flag::class, $flagIds);
        $this->em->clear();
        $grid = $this->flagGridFactory->create(AdminRoleConstant::ROLE_FLAG);
        $grid->createView();

        $this->assertSame($flagIds, array_values(array_map(static fn (array $row): int => $row['f']['id'], $grid->getRows())));
    }
}
