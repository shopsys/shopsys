<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\ClosedDayFormType;
use App\Model\Store\ClosedDay\ClosedDayDataFactory;
use App\Model\Store\ClosedDay\ClosedDayFacade;
use App\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use App\Model\Store\ClosedDay\Grid\ClosedDayGridFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClosedDayController extends AdminBaseController
{
    /**
     * @param \App\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     * @param \App\Model\Store\ClosedDay\Grid\ClosedDayGridFactory $closedDayGridFactory
     * @param \App\Model\Store\ClosedDay\ClosedDayDataFactory $closedDayDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        private readonly ClosedDayFacade $closedDayFacade,
        private readonly ClosedDayGridFactory $closedDayGridFactory,
        private readonly ClosedDayDataFactory $closedDayDataFactory,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        private readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/closed-day/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('Admin/Content/ClosedDay/list.html.twig', [
            'gridView' => $this->closedDayGridFactory->create($this->adminDomainTabsFacade->getSelectedDomainId())->createView(),
        ]);
    }

    /**
     * @Route("/closed-day/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('Admin/Content/ClosedDay/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/closed-day/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        return $this->render('Admin/Content/ClosedDay/edit.html.twig', [
            'form' => $form->createView(),
            'closedDay' => $closedDay,
        ]);
    }

    /**
     * @Route("/closed-day/delete/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
