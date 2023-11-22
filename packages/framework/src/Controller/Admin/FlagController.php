<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit;
use Symfony\Component\Routing\Annotation\Route;

class FlagController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagInlineEdit $flagInlineEdit
     */
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly FlagInlineEdit $flagInlineEdit,
    ) {
    }

    /**
     * @Route("/product/flag/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): \Symfony\Component\HttpFoundation\Response
    {
        $productInlineEdit = $this->flagInlineEdit;

        $grid = $productInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Flag/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/product/flag/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        try {
            $fullName = $this->flagFacade->getById($id)->getName();

            $this->flagFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Flag <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (FlagNotFoundException $ex) {
            $this->addErrorFlash(t('Selected flag doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_flag_list');
    }
}
