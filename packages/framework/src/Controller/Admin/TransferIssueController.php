<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Transfer\TransferIssueSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_TRANSFER)]
class TransferIssueController extends AdminBaseController
{
    public function __construct(
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/transfer/issue/list/', name: 'admin_transferissue_list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $administrator = $this->getCurrentAdministrator();

        $this->administratorFacade->setAdministratorTransferIssuesLastSeenDateTime($administrator);

        $queryBuilder = $this->transferIssueFacade->getTransferIssuesQueryBuilderForDataGrid();

        $form = $this->createForm(TransferIssueSearchFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filteredTransfer = $form->getData()['transfer'];

            if ($filteredTransfer !== null) {
                $queryBuilder
                    ->andWhere('ti.transfer = :transfer')
                    ->setParameter('transfer', $filteredTransfer);
            }
        }
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'ti.id');

        $grid = $this->gridFactory->create('transferIssueList', $dataSource, AdminRoleConstant::ROLE_TRANSFER);
        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt DESC, id');

        $grid->addColumn('transfer', 't.name', t('Transfer'));
        $grid->addColumn('message', 'ti.message', t('Message text'));
        $grid->addColumn('createdAt', 'ti.createdAt', t('Date and time'));
        $grid->addDeleteActionColumn('admin_transferissue_delete', ['id' => 'ti.id'])
            ->setConfirmMessage(t('Do you really want to mark this issue as resolved?'));

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysAdministration/content/transfer/issue/list.html.twig', [
            'form' => $form->createView(),
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/transfer/issue/delete/{id}', name: 'admin_transferissue_delete', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $this->transferIssueFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Transfer problem has been marked as resolved'),
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Selected transfer issue was not found'));
        }

        return $this->redirectToRoute('admin_transferissue_list');
    }
}
