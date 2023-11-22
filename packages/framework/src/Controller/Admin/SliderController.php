<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Slider\SliderItemFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SliderController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface $sliderItemDataFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly SliderItemFacade $sliderItemFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly SliderItemDataFactoryInterface $sliderItemDataFactory,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @Route("/slider/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): \Symfony\Component\HttpFoundation\Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(SliderItem::class, 's')
            ->where('s.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId())
            ->orderBy('s.position')
            ->addOrderBy('s.id');
        $dataSource = new QueryBuilderDataSource($queryBuilder, 's.id');

        $grid = $this->gridFactory->create('sliderItemList', $dataSource);
        $grid->enableDragAndDrop(SliderItem::class);

        $grid->addColumn('name', 's.name', t('Name'));
        $grid->addColumn('link', 's.link', t('Link'));
        $grid->addEditActionColumn('admin_slider_edit', ['id' => 's.id']);
        $grid->addDeleteActionColumn('admin_slider_delete', ['id' => 's.id'])
            ->setConfirmMessage(t('Do you really want to remove this page?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Slider/listGrid.html.twig');

        return $this->render('@ShopsysFramework/Admin/Content/Slider/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/slider/item/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): \Symfony\Component\HttpFoundation\Response
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

        return $this->render('@ShopsysFramework/Admin/Content/Slider/new.html.twig', [
            'form' => $form->createView(),
            'selectedDomainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
    }

    /**
     * @Route("/slider/item/edit/{id}", requirements={"id"="\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
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

        return $this->render('@ShopsysFramework/Admin/Content/Slider/edit.html.twig', [
            'form' => $form->createView(),
            'sliderItem' => $sliderItem,
        ]);
    }

    /**
     * @Route("/slider/item/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id): \Symfony\Component\HttpFoundation\RedirectResponse
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
