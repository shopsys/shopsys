<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Flag\FlagFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_FLAG)]
class FlagController extends AdminBaseController
{
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly FlagGridFactory $flagGridFactory,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly FlagDataFactory $flagDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/product/flag/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->flagGridFactory->create(AdminRoleConstant::ROLE_FLAG);

        return $this->render('@ShopsysAdministration/content/flag/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/product/flag/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $flagData = $this->flagDataFactory->create();

        $form = $this->createForm(FlagFormType::class, $flagData, [
            'flag' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->domain->hasAdminAllDomainsEnabled()) {
                $this->addErrorFlash(t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'));

                return $this->redirectToRoute('admin_flag_new');
            }

            $flag = $this->flagFacade->create($flagData);

            $this
                ->addSuccessFlashTwig(
                    t('Flag <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $flag->getName(),
                        'url' => $this->generateUrl('admin_flag_edit', ['id' => $flag->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_flag_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/flag/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/product/flag/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $flag = $this->flagFacade->getById($id);
        $flagData = $this->flagDataFactory->createFromFlag($flag);

        $form = $this->createForm(FlagFormType::class, $flagData, [
            'flag' => $flag,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->flagFacade->edit($id, $flagData);

            $this
                ->addSuccessFlashTwig(
                    t('Flag <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $flag->getName(),
                        'url' => $this->generateUrl('admin_flag_edit', ['id' => $flag->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_flag_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing flag - {{ name }}', ['{{ name }}' => $flag->getName()]));

        return $this->render('@ShopsysAdministration/content/flag/edit.html.twig', [
            'form' => $form->createView(),
            'flag' => $flag,
        ]);
    }

    #[Route(path: '/product/flag/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $flag = $this->flagFacade->getById($id);
            $flagDependencies = $this->flagFacade->getFlagDependencies($flag->getId());
            $hasDependency = $flagDependencies->hasPromoCodeDependency
                || $flagDependencies->hasSeoMixDependency
                || $flagDependencies->hasPromotionXyDependency;

            if ($hasDependency) {
                return $this->render('@ShopsysAdministration/content/flag/deleteForbidden.html.twig', [
                    'hasPromoCodeDependency' => $flagDependencies->hasPromoCodeDependency,
                    'hasSeoMixDependency' => $flagDependencies->hasSeoMixDependency,
                    'hasPromotionXyDependency' => $flagDependencies->hasPromotionXyDependency,
                ]);
            }
            $message = t('Do you really want to remove this flag?');

            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_flag_delete',
                $id,
            );
        } catch (FlagNotFoundException $ex) {
            return new Response(t('Selected flag doesn\'t exist.'));
        }
    }

    #[Route(path: '/product/flag/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $flag = $this->flagFacade->getById($id);
            $fullName = $flag->getName();
            $flagDependencies = $this->flagFacade->getFlagDependencies($flag->getId());

            if ($flagDependencies->hasSeoMixDependency
                || $flagDependencies->hasPromoCodeDependency
                || $flagDependencies->hasPromotionXyDependency
                || $flag->isLockedForDeletion()
            ) {
                $this->addErrorFlashTwig(
                    t('Flag <strong>{{ name }}</strong> cannot be deleted'),
                    [
                        'name' => $fullName,
                    ],
                );

                return $this->redirectToRoute('admin_flag_list');
            }

            $this->flagFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Flag <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (FlagNotFoundException) {
            $this->addErrorFlash(t('Selected flag doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_flag_list');
    }
}
