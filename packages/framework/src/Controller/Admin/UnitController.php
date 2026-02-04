<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Unit\UnitSettingFormType;
use Shopsys\FrameworkBundle\Model\Product\Unit\Exception\UnitNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_UNIT)]
class UnitController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitInlineEdit $unitInlineEdit
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly UnitFacade $unitFacade,
        protected readonly UnitInlineEdit $unitInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/unit/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $unitInlineEdit = $this->unitInlineEdit;

        $grid = $unitInlineEdit->getGrid();

        return $this->render('@ShopsysAdministration/content/unit/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/unit/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $unit = $this->unitFacade->getById($id);
            $isUnitDefault = $this->unitFacade->isUnitDefault($unit);

            if ($isUnitDefault || $this->unitFacade->isUnitUsed($unit)) {
                if ($isUnitDefault) {
                    $message = t(
                        'Unit "%name%" set as default. For deleting existing unit you have to choose new default unit. '
                        . 'Which unit you want to set instead?',
                        ['%name%' => $unit->getName()],
                    );
                } else {
                    $message = t(
                        'For deleting unit "%name%" you have to choose other one to be set everywhere where the existing one is used. '
                        . 'Which unit you want to set instead?',
                        ['%name%' => $unit->getName()],
                    );
                }

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_unit_delete',
                    $id,
                    $this->unitFacade->getAllExceptId($id),
                );
            }
            $message = t(
                'Do you really want to remove unit "%name%" permanently? It is not used anywhere.',
                ['%name%' => $unit->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_unit_delete', $id);
        } catch (UnitNotFoundException $ex) {
            return new Response(t('Selected unit doesn\'t exist'));
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/unit/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(Request $request, int $id): Response
    {
        $newId = $request->query->has('newId') ? $request->query->getInt('newId') : null;

        try {
            $fullName = $this->unitFacade->getById($id)->getName();

            $this->unitFacade->deleteById($id, $newId);

            if ($newId === null) {
                $this->addSuccessFlashTwig(
                    t('Unit <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $fullName,
                    ],
                );
            } else {
                $newUnit = $this->unitFacade->getById($newId);
                $this->addSuccessFlashTwig(
                    t('Unit <strong>{{ name }}</strong> deleted and replaced by unit <strong>{{ newName }}</strong>'),
                    [
                        'name' => $fullName,
                        'newName' => $newUnit->getName(),
                    ],
                );
            }
        } catch (UnitNotFoundException $ex) {
            $this->addErrorFlash(t('Selected unit doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_unit_list');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingAction(Request $request): Response
    {
        try {
            $defaultUnit = $this->unitFacade->getDefaultUnit();
        } catch (UnitNotFoundException $ex) {
            $defaultUnit = null;
        }
        $unitSettingsFormData = ['defaultUnit' => $defaultUnit];

        $form = $this->createForm(UnitSettingFormType::class, $unitSettingsFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $unitSettingsFormData = $form->getData();

            $this->unitFacade->setDefaultUnit($unitSettingsFormData['defaultUnit']);

            $this->addSuccessFlash(t('Default unit settings modified'));

            return $this->redirectToRoute('admin_unit_list');
        }

        return $this->render('@ShopsysAdministration/content/unit/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
