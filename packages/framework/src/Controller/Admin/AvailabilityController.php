<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Product\Availability\AvailabilitySettingFormType;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityInlineEdit;
use Shopsys\FrameworkBundle\Model\Product\Availability\Exception\AvailabilityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvailabilityController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityInlineEdit $availabilityInlineEdit
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        protected readonly AvailabilityFacade $availabilityFacade,
        protected readonly AvailabilityInlineEdit $availabilityInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
    ) {
    }

    /**
     * @Route("/product/availability/list/")
     */
    public function listAction()
    {
        $grid = $this->availabilityInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Availability/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/product/availability/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function deleteAction(Request $request, $id)
    {
        $newId = $request->get('newId');

        try {
            $fullName = $this->availabilityFacade->getById($id)->getName();

            $this->availabilityFacade->deleteById($id, $newId);

            if ($newId === null) {
                $this->addSuccessFlashTwig(
                    t('Availability <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $fullName,
                    ],
                );
            } else {
                $newAvailability = $this->availabilityFacade->getById($newId);
                $this->addSuccessFlashTwig(
                    t('Availability <strong>{{ oldName }}</strong> deleted and replaced by availability <strong>{{ newName }}</strong>'),
                    [
                        'oldName' => $fullName,
                        'newName' => $newAvailability->getName(),
                    ],
                );
            }
        } catch (AvailabilityNotFoundException $ex) {
            $this->addErrorFlash(t('Selected availatibily doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_availability_list');
    }

    /**
     * @Route("/product/availability/delete-confirm/{id}", requirements={"id" = "\d+"})
     * @param int $id
     */
    public function deleteConfirmAction($id)
    {
        try {
            $availability = $this->availabilityFacade->getById($id);
            $isAvailabilityDefault = $this->availabilityFacade->isAvailabilityDefault($availability);
            if ($this->availabilityFacade->isAvailabilityUsed($availability) || $isAvailabilityDefault) {
                if ($isAvailabilityDefault) {
                    $message = t(
                        'Availability "%name%" set as default. For deleting it you have to choose other one to be set everywhere '
                        . 'where the existing one is used. Which availability you want to set instead?',
                        ['%name%' => $availability->getName()],
                    );
                } else {
                    $message = t(
                        'Because availability "%name%"  is used with other products also, you have to choose a new availability '
                        . 'which will replace the existing one. Which availability you want to set to these products?',
                        ['%name%' => $availability->getName()],
                    );
                }

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_availability_delete',
                    $id,
                    $this->availabilityFacade->getAllExceptId($id),
                );
            }
            $message = t(
                'Do you really want to remove availability "%name%" permanently? It is not used anywhere.',
                ['%name%' => $availability->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_availability_delete',
                $id,
            );
        } catch (AvailabilityNotFoundException $ex) {
            return new Response(t('Selected availability doesn\'t exist'));
        }
    }

    /**
     * @Route("/product/availability/setting/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingAction(Request $request)
    {
        try {
            $defaultInStockAvailability = $this->availabilityFacade->getDefaultInStockAvailability();
        } catch (AvailabilityNotFoundException $ex) {
            $defaultInStockAvailability = null;
        }
        $availabilitySettingsFormData['defaultInStockAvailability'] = $defaultInStockAvailability;

        $form = $this->createForm(AvailabilitySettingFormType::class, $availabilitySettingsFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitySettingsFormData = $form->getData();

            $this->availabilityFacade->setDefaultInStockAvailability(
                $availabilitySettingsFormData['defaultInStockAvailability'],
            );

            $this->addSuccessFlash(t('Default availability for the stock settings modified'));

            return $this->redirectToRoute('admin_availability_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Availability/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
