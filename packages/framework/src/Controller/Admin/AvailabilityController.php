<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Form\Admin\Product\Availability\AvailabilitySettingFormType;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AvailabilityController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory
     */
    private $confirmDeleteResponseFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade
     */
    private $availabilityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityInlineEdit
     */
    private $availabilityInlineEdit;

    public function __construct(
        AvailabilityFacade $availabilityFacade,
        AvailabilityInlineEdit $availabilityInlineEdit,
        ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
    ) {
        $this->availabilityFacade = $availabilityFacade;
        $this->availabilityInlineEdit = $availabilityInlineEdit;
        $this->confirmDeleteResponseFactory = $confirmDeleteResponseFactory;
    }

    public function listAction()
    {
        $grid = $this->availabilityInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Availability/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    public function deleteAction(Request $request, int $id)
    {
        $newId = $request->get('newId');

        try {
            $fullName = $this->availabilityFacade->getById($id)->getName();

            $this->availabilityFacade->deleteById($id, $newId);

            if ($newId === null) {
                $this->getFlashMessageSender()->addSuccessFlashTwig(
                    t('Availability <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $fullName,
                    ]
                );
            } else {
                $newAvailability = $this->availabilityFacade->getById($newId);
                $this->getFlashMessageSender()->addSuccessFlashTwig(
                    t('Availability <strong>{{ oldName }}</strong> deleted and replaced by availability <strong>{{ newName }}</strong>'),
                    [
                        'oldName' => $fullName,
                        'newName' => $newAvailability->getName(),
                    ]
                );
            }
        } catch (\Shopsys\FrameworkBundle\Model\Product\Availability\Exception\AvailabilityNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Selected availatibily doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_availability_list');
    }

    public function deleteConfirmAction(int $id)
    {
        try {
            $availability = $this->availabilityFacade->getById($id);
            $isAvailabilityDefault = $this->availabilityFacade->isAvailabilityDefault($availability);
            if ($this->availabilityFacade->isAvailabilityUsed($availability) || $isAvailabilityDefault) {
                if ($isAvailabilityDefault) {
                    $message = t(
                        'Availability "%name%" set as default. For deleting it you have to choose other one to be set everywhere '
                        . 'where the existing one is used. Which availability you want to set instead?',
                        ['%name%' => $availability->getName()]
                    );
                } else {
                    $message = t(
                        'Because availability "%name%"  is used with other products also, you have to choose a new availability '
                        . 'which will replace the existing one. Which availability you want to set to these products?',
                        ['%name%' => $availability->getName()]
                    );
                }

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_availability_delete',
                    $id,
                    $this->availabilityFacade->getAllExceptId($id)
                );
            } else {
                $message = t(
                    'Do you really want to remove availability "%name%" permanently? It is not used anywhere.',
                    ['%name%' => $availability->getName()]
                );

                return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_availability_delete', $id);
            }
        } catch (\Shopsys\FrameworkBundle\Model\Product\Availability\Exception\AvailabilityNotFoundException $ex) {
            return new Response(t('Selected availability doesn\'t exist'));
        }
    }

    public function settingAction(Request $request)
    {
        try {
            $defaultInStockAvailability = $this->availabilityFacade->getDefaultInStockAvailability();
        } catch (\Shopsys\FrameworkBundle\Model\Product\Availability\Exception\AvailabilityNotFoundException $ex) {
            $defaultInStockAvailability = null;
        }
        $availabilitySettingsFormData['defaultInStockAvailability'] = $defaultInStockAvailability;

        $form = $this->createForm(AvailabilitySettingFormType::class, $availabilitySettingsFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitySettingsFormData = $form->getData();

            $this->availabilityFacade->setDefaultInStockAvailability($availabilitySettingsFormData['defaultInStockAvailability']);

            $this->getFlashMessageSender()->addSuccessFlash(t('Default availability for the stock settings modified'));

            return $this->redirectToRoute('admin_availability_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Availability/setting.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
