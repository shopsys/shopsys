<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Flag\FlagFormType;
use App\Model\Product\Flag\FlagDataFactory;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Flag\FlagGridFactory;
use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\FlagController as BaseFlagController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 */
class FlagController extends BaseFlagController
{
    /**
     * @var \App\Model\Product\Flag\FlagDataFactory
     */
    private FlagDataFactory $flagDataFactory;

    /**
     * @var \App\Model\Product\Flag\FlagGridFactory
     */
    private FlagGridFactory $flagGridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider
     */
    private BreadcrumbOverrider $breadcrumbOverrider;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory
     */
    private ConfirmDeleteResponseFactory $confirmDeleteResponseFactory;

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit $flagInlineEdit
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     * @param \App\Model\Product\Flag\FlagGridFactory $flagGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     */
    public function __construct(
        FlagFacade $flagFacade,
        FlagInlineEdit $flagInlineEdit,
        FlagDataFactory $flagDataFactory,
        FlagGridFactory $flagGridFactory,
        BreadcrumbOverrider $breadcrumbOverrider,
        ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
    ) {
        parent::__construct($flagFacade, $flagInlineEdit);

        $this->flagDataFactory = $flagDataFactory;
        $this->flagGridFactory = $flagGridFactory;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
        $this->confirmDeleteResponseFactory = $confirmDeleteResponseFactory;
    }

    /**
     * @Route("/product/flag/delete-confirm/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteConfirmAction(int $id): Response
    {
        try {
            $flag = $this->flagFacade->getById($id);
            $flagDependencies = $this->flagFacade->getFlagDependencies($flag->getId());
            $hasDependency = $flagDependencies->hasPromoCodeDependency || $flagDependencies->hasSeoMixDependency;

            if ($hasDependency) {
                return $this->render('Admin/Content/Flag/deleteForbidden.html.twig', [
                    'hasPromoCodeDependency' => $flagDependencies->hasPromoCodeDependency,
                    'hasSeoMixDependency' => $flagDependencies->hasSeoMixDependency,
                ]);
            }
            $message = t('Do you really want to remove this flag?');
            return $this->confirmDeleteResponseFactory->createDeleteResponse(
                $message,
                'admin_flag_delete',
                $id
            );
        } catch (FlagNotFoundException $ex) {
            return new Response(t('Selected flag doesn\'t exist.'));
        }
    }

    /**
     * @Route("/product/flag/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id): Response
    {
        try {
            $flag = $this->flagFacade->getById($id);
            $fullName = $flag->getName();

            $flagDependencies = $this->flagFacade->getFlagDependencies($flag->getId());
            if ($flagDependencies->hasSeoMixDependency || $flagDependencies->hasPromoCodeDependency) {
                $this->addErrorFlash(t('The selected flag cannot be deleted.'));
                return $this->redirectToRoute('admin_flag_list');
            }

            $this->flagFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Flag <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (FlagNotFoundException $ex) {
            $this->addErrorFlash(t('Selected flag doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_flag_list');
    }

    /**
     * @Route("/product/flag/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->flagGridFactory->create();

        return $this->render('Admin/Content/Flag/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/product/flag/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                    ]
                );
            return $this->redirectToRoute('admin_flag_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing flag - {{ name }}', ['{{ name }}' => $flag->getName()]));

        return $this->render('Admin/Content/Flag/edit.html.twig', [
            'form' => $form->createView(),
            'flag' => $flag,
        ]);
    }

    /**
     * @Route("/product/flag/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $flagData = $this->flagDataFactory->create();

        $form = $this->createForm(FlagFormType::class, $flagData, [
            'flag' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flag = $this->flagFacade->create($flagData);

            $this
                ->addSuccessFlashTwig(
                    t('Flag <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $flag->getName(),
                        'url' => $this->generateUrl('admin_flag_edit', ['id' => $flag->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_flag_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/Flag/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
