<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdvertController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Twig\ImageExtension $imageExtension
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactoryInterface $advertDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ImageExtension $imageExtension,
        protected readonly AdvertDataFactoryInterface $advertDataFactory,
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
        protected readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @Route("/advert/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function editAction(Request $request, $id)
    {
        $advert = $this->advertFacade->getById($id);

        $advertData = $this->advertDataFactory->createFromAdvert($advert);

        $form = $this->createForm(AdvertFormType::class, $advertData, [
            'image_exists' => $this->imageExtension->imageExists($advert),
            'scenario' => AdvertFormType::SCENARIO_EDIT,
            'advert' => $advert,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->advertFacade->edit($id, $advertData);

            $this->addSuccessFlashTwig(
                t('Advertising <a href="{{ url }}"><strong>{{ name }}</strong></a> modified'),
                [
                    'name' => $advert->getName(),
                    'url' => $this->generateUrl('admin_advert_edit', ['id' => $advert->getId()]),
                ]
            );
            return $this->redirectToRoute('admin_advert_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing advertising - %name%', ['%name%' => $advert->getName()])
        );

        return $this->render('@ShopsysFramework/Admin/Content/Advert/edit.html.twig', [
            'form' => $form->createView(),
            'advert' => $advert,
        ]);
    }

    /**
     * @Route("/advert/list/")
     */
    public function listAction()
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'a.id',
            function ($row) {
                $advert = $this->advertFacade->getById($row['a']['id']);
                $row['advert'] = $advert;
                return $row;
            }
        );

        $grid = $this->gridFactory->create('advertList', $dataSource);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('visible', 'a.hidden', t('Visibility'), true)->setClassAttribute('table-col table-col-10');
        $grid->addColumn('name', 'a.name', t('Name'), true);
        $grid->addColumn('preview', 'a.id', t('Preview'), false);
        $grid->addColumn('positionName', 'a.positionName', t('Area'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_advert_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_advert_delete', ['id' => 'a.id'])
            ->setConfirmMessage(t('Do you really want to remove this advert?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Advert/listGrid.html.twig', [
            'advertPositionNames' => $this->advertPositionRegistry->getAllLabelsIndexedByNames(),
            'TYPE_IMAGE' => Advert::TYPE_IMAGE,
        ]);

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $this->render('@ShopsysFramework/Admin/Content/Advert/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    /**
     * @Route("/advert/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function newAction(Request $request)
    {
        $advertData = $this->advertDataFactory->create();
        $advertData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $form = $this->createForm(AdvertFormType::class, $advertData, [
            'scenario' => AdvertFormType::SCENARIO_CREATE,
            'advert' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertData = $form->getData();

            $advert = $this->advertFacade->create($advertData);

            $this
                ->addSuccessFlashTwig(
                    t('Advertising <a href="{{ url }}"><strong>{{ name }}</strong></a> created'),
                    [
                        'name' => $advert->getName(),
                        'url' => $this->generateUrl('admin_advert_edit', ['id' => $advert->getId()]),
                    ]
                );
            return $this->redirectToRoute('admin_advert_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Advert/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/advert/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     */
    public function deleteAction($id)
    {
        try {
            $fullName = $this->advertFacade->getById($id)->getName();

            $this->advertFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Advertising <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (AdvertNotFoundException $ex) {
            $this->addErrorFlash(t('Selected advertisement doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_advert_list');
    }
}
