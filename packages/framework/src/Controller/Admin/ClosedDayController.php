<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Store\ClosedDayFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Grid\ClosedDayGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClosedDayController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\Grid\ClosedDayGridFactory $closedDayGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory $closedDayDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly ClosedDayGridFactory $closedDayGridFactory,
        protected readonly ClosedDayDataFactory $closedDayDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/closed-day/list/')]
    public function listAction(): Response
    {
        return $this->render('@ShopsysFramework/Admin/Content/ClosedDay/list.html.twig', [
            'gridView' => $this->closedDayGridFactory->create($this->adminDomainTabsFacade->getSelectedDomainId())->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/closed-day/new/')]
    public function newAction(Request $request): Response
    {
        $closedDayData = $this->closedDayDataFactory->create();
        $closedDayData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(ClosedDayFormType::class, $closedDayData)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $closedDay = $this->closedDayFacade->create($closedDayData);

            $this->addSuccessFlashTwig(
                t('Holiday / internal day <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'url' => $this->generateUrl('admin_closedday_edit', ['id' => $closedDay->getId()]),
                    'name' => $closedDay->getName(),
                ],
            );

            return $this->redirectToRoute('admin_closedday_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/ClosedDay/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/closed-day/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $closedDay = $this->closedDayFacade->getById($id);
        $closedDayData = $this->closedDayDataFactory->createFromClosedDay($closedDay);

        $form = $this->createForm(ClosedDayFormType::class, $closedDayData)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $closedDay = $this->closedDayFacade->edit($closedDay->getId(), $closedDayData);

            $this->addSuccessFlashTwig(
                t('Holiday / internal day <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'url' => $this->generateUrl('admin_closedday_edit', ['id' => $closedDay->getId()]),
                    'name' => $closedDay->getName(),
                ],
            );

            return $this->redirectToRoute('admin_closedday_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t(
                'Editing holiday / internal day - {{ name }}',
                [
                    '{{ name }}' => $closedDay->getName(),
                ],
            ),
        );

        return $this->render('@ShopsysFramework/Admin/Content/ClosedDay/edit.html.twig', [
            'form' => $form->createView(),
            'closedDay' => $closedDay,
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/closed-day/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $closedDay = $this->closedDayFacade->getById($id);
            $closedDayName = $closedDay->getName();

            $this->closedDayFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t(
                    'Holiday / internal day <strong>{{ name }}</strong> deleted',
                    [
                        '{{ name }}' => $closedDayName,
                    ],
                ),
            );
        } catch (ClosedDayNotFoundException) {
            $this->addErrorFlash(t('Selected holiday / internal day doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_closedday_list');
    }
}
