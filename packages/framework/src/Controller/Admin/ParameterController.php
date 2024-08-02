<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParameterController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory $parameterGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactoryInterface $parameterDataFactory
     */
    public function __construct(
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ParameterGridFactory $parameterGridFactory,
        protected readonly ParameterDataFactoryInterface $parameterDataFactory,
    ) {
    }

    #[Route(path: '/product/parameter/list/')]
    public function listAction()
    {
        $grid = $this->parameterGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Parameter/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/parameter/new/')]
    public function newAction(Request $request): Response
    {
        $parameterData = $this->parameterDataFactory->create();

        $form = $this->createForm(ParameterFormType::class, $parameterData, [
            'parameter' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameter = $this->parameterFacade->create($parameterData);

            $this->addSuccessFlashTwig(
                t('Parameter <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $parameter->getName(),
                    'url' => $this->generateUrl('admin_parameter_edit', ['id' => $parameter->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_parameter_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Parameter/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/product/parameter/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $parameter = $this->parameterFacade->getById($id);
        $parameterData = $this->parameterDataFactory->createFromParameter($parameter);

        $form = $this->createForm(ParameterFormType::class, $parameterData, [
            'parameter' => $parameter,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameter = $this->parameterFacade->edit($id, $parameterData);

            $this->addSuccessFlashTwig(
                t('Parameter <strong><a href="{{ url }}">{{ name }}</a></strong> edited'),
                [
                    'name' => $parameter->getName(),
                    'url' => $this->generateUrl('admin_parameter_edit', ['id' => $parameter->getId()]),
                ],
            );

            if ($parameter->isSlider() && $this->parameterFacade->getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder($parameter) > 0) {
                return $this->redirectToRoute('admin_parametervalues_edit', ['id' => $parameter->getId()]);
            }

            return $this->redirectToRoute('admin_parameter_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Parameter/edit.html.twig', [
            'form' => $form->createView(),
            'parameter' => $parameter,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/product/parameter/delete/{id}', requirements: ['id' => '\d+'])]
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
