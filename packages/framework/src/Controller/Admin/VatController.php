<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Vat\VatSettingsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_VAT)]
class VatController extends AdminBaseController
{
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly VatInlineEdit $vatInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/vat/list/')]
    #[CanView(methods: [HttpMethod::GET])]
    #[CanEdit(methods: [HttpMethod::POST])]
    public function listAction(): Response
    {
        $grid = $this->vatInlineEdit->getGrid();

        return $this->render('@ShopsysAdministration/content/vat/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/vat/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
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

    #[Route(path: '/vat/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(Request $request, int $id): Response
    {
        $newId = $request->query->has('newId') ? $request->query->getInt('newId') : null;

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

    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingsAction(Request $request): Response
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

        return $this->render('@ShopsysAdministration/content/vat/vatSettings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
