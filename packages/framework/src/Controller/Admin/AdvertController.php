<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertNotFoundException;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ADVERT)]
class AdvertController extends AdminBaseController
{
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ImageExtension $imageExtension,
        protected readonly AdvertDataFactory $advertDataFactory,
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    #[Route(path: '/advert/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $advert = $this->advertFacade->getById($id);

        $advertData = $this->advertDataFactory->createFromAdvert($advert);

        $form = $this->createForm(AdvertFormType::class, $advertData, [
            'web_image_exists' => $this->imageExtension->imageExists($advert, AdvertFacade::IMAGE_TYPE_WEB),
            'mobile_image_exists' => $this->imageExtension->imageExists($advert, AdvertFacade::IMAGE_TYPE_MOBILE),
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
                ],
            );

            return $this->redirectToRoute('admin_advert_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing advertising - %name%', ['%name%' => $advert->getName()]),
        );

        return $this->render('@ShopsysAdministration/content/advert/edit.html.twig', [
            'form' => $form->createView(),
            'advert' => $advert,
        ]);
    }

    #[Route(path: '/advert/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Advert::class, 'a')
            ->where('a.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'a.id',
            function ($row) {
                $advert = $this->advertFacade->getById($row['a']['id']);
                $row['advert'] = $advert;

                return $row;
            },
        );

        $grid = $this->gridFactory->create('advertList', $dataSource, AdminRoleConstant::ROLE_ADVERT);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('visible', 'a.hidden', t('Visibility'), true)->setClassAttribute('w-1 text-center');
        $grid->addColumn('name', 'a.name', t('Name'), true);
        $grid->addColumn('preview', 'a.id', t('Preview'), false);
        $grid->addColumn('positionName', 'a.positionName', t('Area'), true);

        $grid->addEditActionColumn('admin_advert_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_advert_delete', ['id' => 'a.id'])
            ->setConfirmMessage(t('Do you really want to remove this advert?'));

        $grid->setTheme('@ShopsysAdministration/content/advert/listGrid.html.twig', [
            'advertPositionNames' => $this->advertPositionRegistry->getAllLabelsIndexedByNames(),
            'TYPE_IMAGE' => Advert::TYPE_IMAGE,
        ]);

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/advert/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }

    #[Route(path: '/advert/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
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
                    ],
                );

            return $this->redirectToRoute('admin_advert_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/advert/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/advert/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->advertFacade->getById($id)->getName();

            $this->advertFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Advertising <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (AdvertNotFoundException $ex) {
            $this->addErrorFlash(t('Selected advertisement doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_advert_list');
    }
}
