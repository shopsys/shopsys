<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\TransferIssueSearchFormType;
use App\Model\Administrator\AdministratorFacade;
use App\Model\Transfer\Issue\TransferIssueFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AdminBaseController
{
    /**
     * @var \App\Model\Transfer\Issue\TransferIssueFacade
     */
    private $transferIssueFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade
     */
    private $administratorGridFacade;

    /**
     * @var \App\Model\Administrator\AdministratorFacade
     */
    private $administratorFacade;

    /**
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \App\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(
        TransferIssueFacade $transferIssueFacade,
        GridFactory $gridFactory,
        AdministratorGridFacade $administratorGridFacade,
        AdministratorFacade $administratorFacade
    ) {
        $this->transferIssueFacade = $transferIssueFacade;
        $this->gridFactory = $gridFactory;
        $this->administratorGridFacade = $administratorGridFacade;
        $this->administratorFacade = $administratorFacade;
    }

    /**
     * @Route("/transfer/list/", name="admin_transfer_list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        /** @var \App\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();

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
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ti.id');

        $grid = $this->gridFactory->create('transferIssueList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt DESC, id');

        $grid->addColumn('transfer', 't.name', t('Přenos'));
        $grid->addColumn('message', 'ti.message', t('Text zprávy'));
        $grid->addColumn('createdAt', 'ti.createdAt', t('Datum a čas'));
        $grid->addDeleteActionColumn('admin_transfer_delete', ['id' => 'ti.id'])
            ->setConfirmMessage(t('Opravdu chcete tento problém označit jako vyřešený?'));

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('Admin/Content/Transfer/list.html.twig', [
            'form' => $form->createView(),
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/transfer/issue/delete/{id}",name="admin_transfer_delete", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $this->transferIssueFacade->deleteById((int)$id);

            $this->addSuccessFlashTwig(
                t('Problém v přenosu byl označen jako vyřešený')
            );
        } catch (NotFoundHttpException $ex) {
            $this->addErrorFlash(t('Vybraný problém v přenosu nebyl nalezen'));
        }

        return $this->redirectToRoute('admin_transfer_list');
    }
}
