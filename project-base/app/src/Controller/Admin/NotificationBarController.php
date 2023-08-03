<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\NotificationBarFormType;
use App\Model\NotificationBar\Exception\NotificationBarNotFoundException;
use App\Model\NotificationBar\NotificationBarDataFactory;
use App\Model\NotificationBar\NotificationBarFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationBarController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \App\Model\NotificationBar\NotificationBarDataFactory $notificationBarDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     */
    public function __construct(
        private AdminDomainTabsFacade $adminDomainTabsFacade,
        private NotificationBarFacade $notificationBarFacade,
        private NotificationBarDataFactory $notificationBarDataFactory,
        private GridFactory $gridFactory,
    ) {
    }

    /**
     * @Route("/notification-bar/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $queryBuilder = $this->notificationBarFacade->getAllByDomainIdQueryBuilderForGrid($this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'nb.id');

        $grid = $this->gridFactory->create('NotificationBarList', $dataSource);

        $grid->addColumn('visible', 'visibility', t('Visibility'), true)->setClassAttribute('table-col table-col-10');
        $grid->addColumn('text', 'nb.text', t('Text'));
        $grid->addColumn('validityFrom', 'nb.validityFrom', t('Valid from'), true);
        $grid->addColumn('validityTo', 'nb.validityTo', t('Valid to'), true);
        $grid->addEditActionColumn('admin_notificationbar_edit', ['id' => 'nb.id']);
        $grid->addDeleteActionColumn('admin_notificationbar_delete', ['id' => 'nb.id'])
            ->setConfirmMessage(t('Do you really want to remove this notification bar?'));

        $grid->setTheme('Admin/Content/NotificationBar/listGrid.html.twig');

        return $this->render('Admin/Content/NotificationBar/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/notification-bar/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $notificationBarData = $this->notificationBarDataFactory->create();
        $notificationBarData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(NotificationBarFormType::class, $notificationBarData, [
            'scenario' => NotificationBarFormType::SCENARIO_CREATE,
            'notification_bar' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->notificationBarFacade->create($notificationBarData);

            $this->addSuccessFlash(t('Notification bar has been successfuly created'));

            return $this->redirectToRoute('admin_notificationbar_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/NotificationBar/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/notification-bar/edit/{id}", requirements={"id"="\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $notificationBar = $this->notificationBarFacade->getById($id);
        $notificationBarData = $this->notificationBarDataFactory->createFromNotificationBar($notificationBar);

        $form = $this->createForm(NotificationBarFormType::class, $notificationBarData, [
            'scenario' => NotificationBarFormType::SCENARIO_EDIT,
            'notification_bar' => $notificationBar,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notificationBar = $this->notificationBarFacade->edit($notificationBar, $notificationBarData);

            $this->addSuccessFlashTwig(
                t('<strong><a href="{{ url }}">Notification bar</a></strong> has been successfuly updated'),
                [
                    'url' => $this->generateUrl('admin_notificationbar_edit', ['id' => $notificationBar->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_notificationbar_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/NotificationBar/edit.html.twig', [
            'form' => $form->createView(),
            'notificationBar' => $notificationBar,
        ]);
    }

    /**
     * @Route("/notification-bar/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $this->notificationBarFacade->delete($id);

            $this->addSuccessFlash(t('Notification bar has been successfuly deleted'));
        } catch (NotificationBarNotFoundException $exception) {
            $this->addErrorFlash(t('Selected notification bar does not exist'));
        }

        return $this->redirectToRoute('admin_notificationbar_list');
    }
}
