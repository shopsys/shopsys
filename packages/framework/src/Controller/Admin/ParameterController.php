<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PARAMETER)]
class ParameterController extends AdminBaseController
{
    public function __construct(
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ParameterGridFactory $parameterGridFactory,
        protected readonly ParameterDataFactory $parameterDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/product/parameter/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $grid = $this->parameterGridFactory->create(AdminRoleConstant::ROLE_PARAMETER);

        return $this->render('@ShopsysAdministration/content/parameter/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/product/parameter/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $parameterData = $this->parameterDataFactory->create();

        $form = $this->createForm(ParameterFormType::class, $parameterData, [
            'parameter' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->domain->hasAdminAllDomainsEnabled()) {
                $this->addErrorFlash(t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'));

                return $this->redirectToRoute('admin_parameter_new');
            }

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

        return $this->render('@ShopsysAdministration/content/parameter/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/product/parameter/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/parameter/edit.html.twig', [
            'form' => $form->createView(),
            'parameter' => $parameter,
        ]);
    }

    #[Route(path: '/product/parameter/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
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
