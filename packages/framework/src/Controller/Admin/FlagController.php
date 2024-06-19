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

    #[Route(path: '/product/flag/list/')]
    public function listAction()
    {
        $productInlineEdit = $this->flagInlineEdit;

        $grid = $productInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Flag/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/product/flag/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction($id)
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
