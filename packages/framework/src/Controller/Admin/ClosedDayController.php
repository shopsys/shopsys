<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Holidays\HolidaysImportFormType;
use Shopsys\FrameworkBundle\Form\Admin\Store\ClosedDayFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportDataFactory;
use Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Exception\ClosedDayNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\Grid\ClosedDayGridFactory;
use Spatie\Holidays\Exceptions\InvalidCountry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CLOSED_DAYS)]
class ClosedDayController extends AdminBaseController
{
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly ClosedDayGridFactory $closedDayGridFactory,
        protected readonly ClosedDayDataFactory $closedDayDataFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly HolidaysImportDataFactory $holidaysImportDataFactory,
        protected readonly HolidaysImportFacade $holidaysImportFacade,
    ) {
    }

    #[Route(path: '/closed-day/list/')]
    #[CanView]
    public function listAction(): Response
    {
        return $this->render('@ShopsysAdministration/content/closedDay/list.html.twig', [
            'gridView' => $this->closedDayGridFactory->create($this->adminDomainTabsFacade->getSelectedDomainId())->createView(),
        ]);
    }

    #[Route(path: '/closed-day/new/')]
    #[CanCreate]
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

        return $this->render('@ShopsysAdministration/content/closedDay/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/closed-day/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $closedDay = $this->closedDayFacade->getById($id);
        $closedDayData = $this->closedDayDataFactory->createFromClosedDay($closedDay);

        $form = $this->createForm(ClosedDayFormType::class, $closedDayData, ['closed_day' => $closedDay])
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

        return $this->render('@ShopsysAdministration/content/closedDay/edit.html.twig', [
            'form' => $form->createView(),
            'closedDay' => $closedDay,
        ]);
    }

    #[Route(path: '/closed-day/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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

    #[Route(path: '/closed-day/holidays-import')]
    #[CanEdit]
    public function holidaysImportAction(Request $request): Response
    {
        $holidaysImportData = $this->holidaysImportDataFactory->create();
        $form = $this->createForm(HolidaysImportFormType::class, $holidaysImportData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $importedCount = $this->holidaysImportFacade->import($data);
                $this->addSuccessFlashTwig(t('{1} Imported <strong>%count%</strong> holiday.|[2,Inf] Imported <strong>%count%</strong> holidays.', ['%count%' => $importedCount]));
            } catch (InvalidCountry) {
                $this->addErrorFlash(t('The selected country is not valid.'));
            }

            return $this->redirectToRoute('admin_closedday_list');
        }

        return $this->render('@ShopsysAdministration/content/closedDay/holidaysImport.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
