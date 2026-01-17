<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\GiftPlan\GiftPlanFormType;
use Shopsys\FrameworkBundle\Form\Admin\GiftPlan\GiftPriceSettingFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\Exception\GiftPlanNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanDataFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_GIFT_PLAN)]
class GiftPlanController extends AdminBaseController
{
    public function __construct(
        protected readonly GiftPlanFacade $giftPlanFacade,
        protected readonly GiftPlanDataFactory $giftPlanDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly EntityManagerInterface $em,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/gift-plan/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $giftPlan = $this->giftPlanFacade->getById($id);

        $giftPlanData = $this->giftPlanDataFactory->createFromGiftPlan($giftPlan);

        $form = $this->createForm(GiftPlanFormType::class, $giftPlanData, [
            'giftPlan' => $giftPlan,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->giftPlanFacade->edit($id, $giftPlanData);

            $this->addSuccessFlashTwig(
                t('Gift plan <a href="{{ url }}"><strong>{{ name }}</strong></a> modified'),
                [
                    'name' => $giftPlan->getName(),
                    'url' => $this->generateUrl('admin_giftplan_edit', ['id' => $giftPlan->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_giftplan_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing gift plan - %name%', ['%name%' => $giftPlan->getName()]),
        );

        return $this->render('@ShopsysAdministration/content/giftPlan/edit.html.twig', [
            'form' => $form->createView(),
            'giftPlan' => $giftPlan,
        ]);
    }

    #[Route(path: '/gift-plan/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $grid = $this->getGiftPlanGrid($selectedDomainId);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/giftPlan/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    protected function getGiftPlanGrid(int $selectedDomainId): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('gp')
            ->from(GiftPlan::class, 'gp')
            ->where('gp.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $selectedDomainId);
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'gp.id');

        $grid = $this->gridFactory->create('giftPlanList', $dataSource, AdminRoleConstant::ROLE_GIFT_PLAN);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'gp.name', t('Name'), true);

        $grid->addEditActionColumn('admin_giftplan_edit', ['id' => 'gp.id']);
        $grid->addDeleteActionColumn('admin_giftplan_delete', ['id' => 'gp.id'])
            ->setConfirmMessage(t('Do you really want to remove this gift plan?'));

        $grid->setTheme('@ShopsysAdministration/content/giftPlan/listGrid.html.twig');

        return $grid;
    }

    #[Route(path: '/gift-plan/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $giftPlanData = $this->giftPlanDataFactory->create();
        $giftPlanData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(GiftPlanFormType::class, $giftPlanData, [
            'giftPlan' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $giftPlanData = $form->getData();
            $giftPlan = $this->giftPlanFacade->create($giftPlanData);

            $this
                ->addSuccessFlashTwig(
                    t('Gift plan <a href="{{ url }}"><strong>{{ name }}</strong></a> created'),
                    [
                        'name' => $giftPlan->getName(),
                        'url' => $this->generateUrl('admin_giftplan_edit', ['id' => $giftPlan->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_giftplan_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/giftPlan/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/gift-plan/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->giftPlanFacade->getById($id)->getName();

            $this->giftPlanFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Gift plan <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (GiftPlanNotFoundException) {
            $this->addErrorFlash(t('Selected gift plan doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_giftplan_list');
    }

    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function setGiftPriceAction(Request $request): Response
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $currencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($selectedDomainId)->getCode();

        $formData = [
            GiftPlanSettingFacade::GIFT_INPUT_PRICE => $this->giftPlanSettingFacade->getInputGiftPrice($selectedDomainId),
        ];

        $form = $this->createForm(GiftPriceSettingFormType::class, $formData, [
            'currency_code' => $currencyCode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = (array)$form->getData();
            $this->giftPlanSettingFacade->setInputGiftPrice($data[GiftPlanSettingFacade::GIFT_INPUT_PRICE], $selectedDomainId);

            $this->addSuccessFlash(t('Gift price saved'));

            return $this->redirectToRoute('admin_giftplan_list');
        }

        return $this->render('@ShopsysAdministration/content/giftPlan/setGiftPrice.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
