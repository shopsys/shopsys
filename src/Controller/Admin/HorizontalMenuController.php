<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\HorizontalMenu\HorizontalMenuItemFormType;
use App\Model\HorizontalMenu\HorizontalMenuItem;
use App\Model\HorizontalMenu\HorizontalMenuItemDataFactory;
use App\Model\HorizontalMenu\HorizontalMenuItemFacade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HorizontalMenuController extends AdminBaseController
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemFacade
     */
    private $horizontalMenuItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory
     */
    private $horizontalMenuItemDataFactory;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemFacade $horizontalMenuItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory
     */
    public function __construct(
        HorizontalMenuItemFacade $horizontalMenuItemFacade,
        GridFactory $gridFactory,
        HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory
    ) {
        $this->horizontalMenuItemFacade = $horizontalMenuItemFacade;
        $this->gridFactory = $gridFactory;
        $this->horizontalMenuItemDataFactory = $horizontalMenuItemDataFactory;
    }

    /**
     * @Route("/horizontal-menu/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->getGrid();

        return $this->render('Admin/Content/HorizontalMenu/itemsList.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/horizontal-menu/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
        $form = $this->createForm(HorizontalMenuItemFormType::class, $horizontalMenuItemData, [
            'horizontalMenuItem' => null,
        ]);

        $form->setData($horizontalMenuItemData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $horizontalMenuItem = $this->horizontalMenuItemFacade->create($horizontalMenuItemData);

            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Byla vytvořena položka horizontálního menu <strong><a href="{{ url }}">{{ name }}</a></strong>'),
                    [
                        'name' => $horizontalMenuItem->getName(),
                        'url' => $this->generateUrl('admin_horizontalmenu_edit', ['id' => $horizontalMenuItem->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_horizontalmenu_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
        }

        return $this->render('Admin/Content/HorizontalMenu/Item/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/horizontal-menu/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $horizontalMenuItem = $this->horizontalMenuItemFacade->getById($id);
        $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createForEntity($horizontalMenuItem);

        $form = $this->createForm(HorizontalMenuItemFormType::class, $horizontalMenuItemData, [
            'horizontalMenuItem' => $horizontalMenuItem,
        ]);

        $form->setData($horizontalMenuItemData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->horizontalMenuItemFacade->edit($id, $horizontalMenuItemData);

            $this->getFlashMessageSender()
                ->addSuccessFlashTwig(
                    t('Byla upravena položka horizontálního menu <strong><a href="{{ url }}">{{ name }}</a></strong>'),
                    [
                        'name' => $horizontalMenuItem->getName(),
                        'url' => $this->generateUrl('admin_horizontalmenu_edit', ['id' => $horizontalMenuItem->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_horizontalmenu_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->getFlashMessageSender()->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
        }

        return $this->render('Admin/Content/HorizontalMenu/Item/edit.html.twig', [
            'form' => $form->createView(),
            'item' => $horizontalMenuItem,
        ]);
    }

    /**
     * @Route("/horizontal-menu/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $horizontalMenuItem = $this->horizontalMenuItemFacade->getById($id);
            $fullName = $horizontalMenuItem->getName();

            $this->horizontalMenuItemFacade->delete($horizontalMenuItem);

            $this->getFlashMessageSender()->addSuccessFlashTwig(
                t('Položka menu <strong>{{ name }}</strong> byla smazána'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (\App\Model\HorizontalMenu\Exception\HorizontalMenuItemNotFoundException $ex) {
            $this->getFlashMessageSender()->addErrorFlash(t('Zvolená položka menu neexistuje.'));
        }

        return $this->redirectToRoute('admin_horizontalmenu_list');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private function getGrid(): Grid
    {
        $queryBuilder = $this->horizontalMenuItemFacade->getOrderedItemsQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'hmi.id');

        $grid = $this->gridFactory->create('horizontalMenuItemsList', $dataSource);

        $grid->addColumn('name', 'hmi.name', t('Název'));

        $grid->addEditActionColumn('admin_horizontalmenu_edit', ['id' => 'hmi.id']);
        $grid->addDeleteActionColumn('admin_horizontalmenu_delete', ['id' => 'hmi.id'])
            ->setConfirmMessage('Opravdu si přejete položku menu smazat?');

        $grid->enableDragAndDrop(HorizontalMenuItem::class);

        return $grid;
    }
}
