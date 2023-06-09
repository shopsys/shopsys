<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterInlineEdit;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterInlineEdit $parameterInlineEdit
     */
    public function __construct(
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ParameterInlineEdit $parameterInlineEdit,
    ) {
    }

    /**
     * @Route("/product/parameter/list/")
     */
    public function listAction()
    {
        $grid = $this->parameterInlineEdit->getGrid();

        return $this->render('@ShopsysFramework/Admin/Content/Parameter/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/product/parameter/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $fullName = $this->parameterFacade->getById($id)->getName();

            $this->parameterFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Parameter <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (ParameterNotFoundException $ex) {
            $this->addErrorFlash(t('Selected parameter doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_parameter_list');
    }
}
