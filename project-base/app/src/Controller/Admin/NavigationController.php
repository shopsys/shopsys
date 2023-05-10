<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\Navigation\NavigationItemFormType;
use App\Model\Navigation\Exception\NavigationItemNotFoundException;
use App\Model\Navigation\NavigationItem;
use App\Model\Navigation\NavigationItemDataFactory;
use App\Model\Navigation\NavigationItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NavigationController extends AdminBaseController
{
    /**
     * @var \App\Model\Navigation\NavigationItemFacade
     */
    private NavigationItemFacade $navigationItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private GridFactory $gridFactory;

    /**
     * @var \App\Model\Navigation\NavigationItemDataFactory
     */
    private NavigationItemDataFactory $navigationItemDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private AdminDomainTabsFacade $adminDomainTabsFacade;

    /**
     * @param \App\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Navigation\NavigationItemDataFactory $navigationItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        NavigationItemFacade $navigationItemFacade,
        GridFactory $gridFactory,
        NavigationItemDataFactory $navigationItemDataFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
        $this->navigationItemFacade = $navigationItemFacade;
        $this->gridFactory = $gridFactory;
        $this->navigationItemDataFactory = $navigationItemDataFactory;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @Route("/navigation/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $grid = $this->getGrid(
            $this->adminDomainTabsFacade->getSelectedDomainId()
        );

        return $this->render('Admin/Content/Navigation/itemsList.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/navigation/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $navigationItemData = $this->navigationItemDataFactory->createNew();
        $navigationItemData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $form = $this->createForm(NavigationItemFormType::class, $navigationItemData, [
            'navigationItem' => null,
        ]);

        $form->setData($navigationItemData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $navigationItem = $this->navigationItemFacade->create($navigationItemData);

            $this
                ->addSuccessFlashTwig(
                    t('Byla vytvořena položka navigace <strong><a href="{{ url }}">{{ name }}</a></strong>'),
                    [
                        'name' => $navigationItem->getName(),
                        'url' => $this->generateUrl('admin_navigation_edit', ['id' => $navigationItem->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_navigation_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
        }

        return $this->render('Admin/Content/Navigation/Item/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/navigation/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $navigationItem = $this->navigationItemFacade->getById($id);
        $navigationItemData = $this->navigationItemDataFactory->createForEntity($navigationItem);

        $form = $this->createForm(NavigationItemFormType::class, $navigationItemData, [
            'navigationItem' => $navigationItem,
        ]);

        $form->setData($navigationItemData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->navigationItemFacade->edit($id, $navigationItemData);

            $this
                ->addSuccessFlashTwig(
                    t('Byla upravena položka navigace <strong><a href="{{ url }}">{{ name }}</a></strong>'),
                    [
                        'name' => $navigationItem->getName(),
                        'url' => $this->generateUrl('admin_navigation_edit', ['id' => $navigationItem->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_navigation_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Prosím zkontrolujte si správnost vyplnění všech údajů'));
        }

        return $this->render('Admin/Content/Navigation/Item/edit.html.twig', [
            'form' => $form->createView(),
            'item' => $navigationItem,
        ]);
    }

    /**
     * @Route("/navigation/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $navigationItem = $this->navigationItemFacade->getById($id);
            $fullName = $navigationItem->getName();

            $this->navigationItemFacade->delete($navigationItem);

            $this->addSuccessFlashTwig(
                t('Položka navigace <strong>{{ name }}</strong> byla smazána'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (NavigationItemNotFoundException $ex) {
            $this->addErrorFlash(t('Zvolená položka navigace neexistuje.'));
        }

        return $this->redirectToRoute('admin_navigation_list');
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    private function getGrid(int $domainId): Grid
    {
        $queryBuilder = $this->navigationItemFacade->getOrderedItemsByDomainQueryBuilder($domainId);

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ni.id');

        $grid = $this->gridFactory->create('navigationItemsList', $dataSource);

        $grid->addColumn('name', 'ni.name', t('Název'));

        $grid->addEditActionColumn('admin_navigation_edit', ['id' => 'ni.id']);
        $grid->addDeleteActionColumn('admin_navigation_delete', ['id' => 'ni.id'])
            ->setConfirmMessage('Opravdu si přejete položku navigace smazat?');

        $grid->enableDragAndDrop(NavigationItem::class);

        return $grid;
    }
}
