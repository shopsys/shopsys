<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Product\Flag\FlagFormType;
use App\Model\Product\Flag\FlagDataFactory;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Flag\FlagGridFactory;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\FlagController as BaseFlagController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit $flagInlineEdit
     * @param \App\Model\Product\Flag\FlagDataFactory $flagDataFactory
     * @param \App\Model\Product\Flag\FlagGridFactory $flagGridFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        FlagFacade $flagFacade,
        FlagInlineEdit $flagInlineEdit,
        FlagDataFactory $flagDataFactory,
        FlagGridFactory $flagGridFactory,
        BreadcrumbOverrider $breadcrumbOverrider
    ) {
        parent::__construct($flagFacade, $flagInlineEdit);

        $this->flagDataFactory = $flagDataFactory;
        $this->flagGridFactory = $flagGridFactory;
        $this->breadcrumbOverrider = $breadcrumbOverrider;
    }

    /**
     * @Route("/product/flag/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @throws \RuntimeException
     */
    public function deleteAction($id)
    {
        throw new RuntimeException('deleteAction() should never be called.');
    }

    /**
     * @Route("/product/flag/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->flagGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Flag/list.html.twig', [
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

        $form = $this->createForm(FlagFormType::class, $flagData);
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
}
