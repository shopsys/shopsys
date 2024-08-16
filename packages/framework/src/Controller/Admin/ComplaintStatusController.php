<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Status\Exception\ComplaintStatusDeletionForbiddenException;
use Shopsys\FrameworkBundle\Model\Complaint\Status\Exception\ComplaintStatusNotFoundException;
use Shopsys\FrameworkBundle\Model\Complaint\Status\Grid\ComplaintStatusInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComplaintStatusController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\Grid\ComplaintStatusInlineEdit $complaintStatusInlineEdit
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly ComplaintStatusInlineEdit $complaintStatusInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint-status/list/')]
    public function listAction(): Response
    {
        $grid = $this->complaintStatusInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/ComplaintStatus/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint-status/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(Request $request, int $id): Response
    {
        $newId = $request->get('newId');
        $newId = $newId !== null ? (int)$newId : null;

        try {
            $complaintStatus = $this->complaintStatusFacade->getById($id);
            $this->complaintStatusFacade->deleteById($id, $newId);

            if ($newId === null) {
                $this->addSuccessFlashTwig(
                    t('Status of complaints <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $complaintStatus->getName(),
                    ],
                );
            } else {
                $newComplaintStatus = $this->complaintStatusFacade->getById($newId);
                $this->addSuccessFlashTwig(
                    t('Status of complaints <strong>{{ oldName }}</strong> replaced by status <strong>{{ newName }}</strong> and deleted.'),
                    [
                        'oldName' => $complaintStatus->getName(),
                        'newName' => $newComplaintStatus->getName(),
                    ],
                );
            }
        } catch (ComplaintStatusDeletionForbiddenException $e) {
            $this->addErrorFlashTwig(
                t('Status of complaints <strong>{{ name }}</strong> is reserved and can\'t be deleted'),
                [
                    'name' => $e->getComplaintStatus()->getName(),
                ],
            );
        } catch (ComplaintStatusNotFoundException) {
            $this->addErrorFlash(t('Selected complaint status doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_complaintstatus_list');
    }

    /**
     * @param int $id
     * @throws \Shopsys\FrameworkBundle\Component\ConfirmDelete\Exception\InvalidEntityPassedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/complaint-status/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $complaintStatus = $this->complaintStatusFacade->getById($id);

            if ($this->complaintStatusFacade->isComplaintStatusUsed($complaintStatus)) {
                $message = t(
                    'Because status "%name%"  is used with other complaints also, you have to choose a new status which will replace '
                    . 'the existing one. Which status you want to set to these complaints? When changing this, there won\'t be emails '
                    . 'sent to customers.',
                    ['%name%' => $complaintStatus->getName()],
                );

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_complaintstatus_delete',
                    $id,
                    $this->complaintStatusFacade->getAllExceptId($id),
                );
            }
            $message = t(
                'Do you really want to remove status of complaint "%name%" permanently? It is not used anywhere.',
                ['%name%' => $complaintStatus->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_complaintstatus_delete',
                $id,
            );
        } catch (ComplaintStatusNotFoundException) {
            return new Response(t('Selected complaint status doesn\'t exist.'));
        }
    }
}
