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
use Shopsys\FrameworkBundle\Form\Admin\Pricing\Group\PricingGroupSettingsFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Exception\PricingGroupNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\Grid\PricingGroupInlineEdit;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PRICING_GROUP)]
class PricingGroupController extends AdminBaseController
{
    public function __construct(
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly PricingGroupFacade $pricingGroupFacade,
        protected readonly PricingGroupInlineEdit $pricingGroupInlineEdit,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Route(path: '/pricing/group/list/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function listAction(): Response
    {
        $grid = $this->pricingGroupInlineEdit->getGrid();

        return $this->render('@ShopsysAdministration/content/pricing/groups/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/pricing/group/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(Request $request, int $id): Response
    {
        $newId = $request->query->has('newId') ? $request->query->getInt('newId') : null;

        try {
            $name = $this->pricingGroupFacade->getById($id)->getName();

            $this->pricingGroupFacade->delete($id, $newId);

            if ($newId === null) {
                $this->addSuccessFlashTwig(
                    t('Pricing group <strong>{{ name }}</strong> deleted'),
                    [
                        'name' => $name,
                    ],
                );
            } else {
                $newPricingGroup = $this->pricingGroupFacade->getById($newId);
                $this->addSuccessFlashTwig(
                    t('Pricing group <strong>{{ name }}</strong> deleted and replaced by group <strong>{{ newName }}</strong>.'),
                    [
                        'name' => $name,
                        'newName' => $newPricingGroup->getName(),
                    ],
                );
            }
        } catch (PricingGroupNotFoundException $ex) {
            $this->addErrorFlash(t('Selected pricing group doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_pricinggroup_list');
    }

    #[Route(path: '/pricing/group/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $pricingGroup = $this->pricingGroupFacade->getById($id);

            $selectedDomain = $this->adminDomainTabsFacade->getSelectedDomainConfig();

            if ($this->pricingGroupSettingFacade->isPricingGroupUsedOnDomain($pricingGroup, $selectedDomain)) {
                $message = t(
                    'For removing pricing group "%name%" you have to choose other one to be set everywhere where the existing one is used. '
                    . 'Which pricing group you want to set instead?',
                    ['%name%' => $pricingGroup->getName()],
                );

                if ($this->pricingGroupSettingFacade->isPricingGroupDefaultOnDomain($pricingGroup, $selectedDomain)) {
                    $message = t(
                        'Pricing group "%name%" set as default. For deleting it you have to choose other one to be set everywhere '
                        . 'where the existing one is used. Which pricing group you want to set instead?',
                        ['%name%' => $pricingGroup->getName()],
                    );
                }

                return $this->confirmDeleteResponseFactory->createSetNewAndDeleteResponse(
                    $message,
                    'admin_pricinggroup_delete',
                    $id,
                    $this->pricingGroupFacade->getAllExceptIdByDomainId($id, $pricingGroup->getDomainId()),
                );
            }
            $message = t(
                'Do you really want to remove pricing group "%name%" permanently? It is not used anywhere.',
                ['%name%' => $pricingGroup->getName()],
            );

            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_pricinggroup_delete',
                $id,
            );
        } catch (PricingGroupNotFoundException $ex) {
            return new Response(t('Selected pricing group doesn\'t exist.'));
        }
    }

    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function settingsAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $pricingGroupSettingsFormData = [
            'defaultPricingGroup' => $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
        ];

        $form = $this->createForm(PricingGroupSettingsFormType::class, $pricingGroupSettingsFormData, [
            'domain_id' => $domainId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pricingGroupSettingsFormData = $form->getData();

            $this->pricingGroupSettingFacade->setDefaultPricingGroupForDomain(
                $pricingGroupSettingsFormData['defaultPricingGroup'],
                $this->adminDomainTabsFacade->getSelectedDomainConfig(),
            );

            $this->addSuccessFlash(t('Default pricing group settings modified'));

            return $this->redirectToRoute('admin_pricinggroup_list');
        }

        return $this->render('@ShopsysAdministration/content/pricing/groups/pricingGroupSettings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
