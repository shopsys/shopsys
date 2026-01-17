<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
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
use Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_SLIDER_ITEM)]
class SliderController extends AdminBaseController
{
    public function __construct(
        protected readonly SliderItemFacade $sliderItemFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly SliderItemDataFactory $sliderItemDataFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/slider/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(SliderItem::class, 's')
            ->where('s.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId())
            ->orderBy('s.position')
            ->addOrderBy('s.id');
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('sliderItemList', $dataSource, AdminRoleConstant::ROLE_SLIDER_ITEM);
        $grid->enableDragAndDrop(SliderItem::class);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->addColumn('link', 's.link', t('Link'));
        $grid->addEditActionColumn('admin_slider_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_slider_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this page?'));

        $grid->setTheme('@ShopsysAdministration/content/slider/listGrid.html.twig');

        return $this->render('@ShopsysAdministration/content/slider/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/slider/item/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $sliderItemData = $this->sliderItemDataFactory->create();
        $sliderItemData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(SliderItemFormType::class, $sliderItemData, [
            'scenario' => SliderItemFormType::SCENARIO_CREATE,
            'slider_item' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sliderItem = $this->sliderItemFacade->create($sliderItemData);

            $this->addSuccessFlashTwig(
                t('Slider page <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $sliderItem->getName(),
                    'url' => $this->generateUrl('admin_slider_edit', ['id' => $sliderItem->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_slider_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/slider/new.html.twig', [
            'form' => $form->createView(),
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }

    #[Route(path: '/slider/item/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $sliderItem = $this->sliderItemFacade->getById($id);
        $sliderItemData = $this->sliderItemDataFactory->createFromSliderItem($sliderItem);

        $form = $this->createForm(SliderItemFormType::class, $sliderItemData, [
            'scenario' => SliderItemFormType::SCENARIO_EDIT,
            'slider_item' => $sliderItem,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sliderItemFacade->edit($id, $sliderItemData);

            $this->addSuccessFlashTwig(
                t('Slider page <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $sliderItem->getName(),
                    'url' => $this->generateUrl('admin_slider_edit', ['id' => $sliderItem->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_slider_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing slider page - %name%', ['%name%' => $sliderItem->getName()]),
        );

        return $this->render('@ShopsysAdministration/content/slider/edit.html.twig', [
            'form' => $form->createView(),
            'sliderItem' => $sliderItem,
        ]);
    }

    #[Route(path: '/slider/item/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $name = $this->sliderItemFacade->getById($id)->getName();

            $this->sliderItemFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Page <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $name,
                ],
            );
        } catch (SliderItemNotFoundException $ex) {
            $this->addErrorFlash(t('Selected page doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_slider_list');
    }
}
