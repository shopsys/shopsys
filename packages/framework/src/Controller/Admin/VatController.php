<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatSettingsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VatController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatInlineEdit $vatInlineEdit
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly VatInlineEdit $vatInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/vat/list/')]
    public function listAction()
    {
        $grid = $this->vatInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Vat/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param int $id
     */
    #[Route(path: '/vat/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id)
    {
        try {
            $vat = $this->vatFacade->getById($id);

            if ($this->vatFacade->isVatUsed($vat)) {
                $message = t(
                    'For deleting rate  "%name%" you have to choose other one to be set everywhere where the existing one is used. '
                    . 'After changing the VAT rate products prices will be recalculated - base price with VAT will remain same. '
                    . 'Which unit you want to set instead?',
                    ['%name%' => $vat->getName()],
                );

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_vat_delete',
                    $id,
                    $this->vatFacade->getAllForDomainExceptId($this->adminDomainTabsFacade->getSelectedDomainId(), $id),
                );
            }
            $message = t(
                'Do you really want to remove rate "%name%" permanently? It is not used anywhere.',
                ['%name%' => $vat->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_vat_delete', $id);
        } catch (VatNotFoundException $ex) {
            return new Response(t('Selected VAT doesn\'t exist'));
        }
    }

    /**
     * @CsrfProtection
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    #[Route(path: '/vat/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(Request $request, $id)
    {
        $newId = $request->get('newId');

        try {
            $fullName = $this->vatFacade->getById($id)->getName();

            $this->vatFacade->deleteById($id, $newId);

            if ($newId === null) {
                $this->addSuccessFlashTwig(
                    t('VAT <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $fullName,
                    ],
                );
            } else {
                $newVat = $this->vatFacade->getById($newId);
                $this->addSuccessFlashTwig(
                    t('VAT <strong>{{ name }}</strong> deleted and replaced by <strong>{{ newName }}</strong>.'),
                    [
                        'name' => $fullName,
                        'newName' => $newVat->getName(),
                    ],
                );
            }
        } catch (VatNotFoundException $ex) {
            $this->addErrorFlash(t('Selected VAT doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_vat_list');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function settingsAction(Request $request)
    {
        $vatSettingsFormData = [
            'defaultVat' => $this->vatFacade->getDefaultVatForDomain(
                $this->adminDomainTabsFacade->getSelectedDomainId(),
            ),
        ];

        $form = $this->createForm(VatSettingsFormType::class, $vatSettingsFormData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vatSettingsFormData = $form->getData();

            $this->vatFacade->setDefaultVatForDomain(
                $vatSettingsFormData['defaultVat'],
                $this->adminDomainTabsFacade->getSelectedDomainId(),
            );

            $this->addSuccessFlash(t('VAT settings modified'));

            return $this->redirectToRoute('admin_vat_list');
        }

        return $this->render('@ShopsysFramework/Admin/Content/Vat/vatSettings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
