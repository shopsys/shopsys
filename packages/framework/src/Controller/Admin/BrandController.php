<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
use Shopsys\FrameworkBundle\Form\Admin\Product\Brand\BrandFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_BRAND)]
class BrandController extends AdminBaseController
{
    public function __construct(
        protected readonly BrandFacade $brandFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly Domain $domain,
        protected readonly BrandDataFactory $brandDataFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/brand/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $brand = $this->brandFacade->getById($id);
        $brandData = $this->brandDataFactory->createFromBrand($brand);

        $form = $this->createForm(BrandFormType::class, $brandData, ['brand' => $brand]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->brandFacade->edit($id, $brandData);

            $this
                ->addSuccessFlashTwig(
                    t('Brand <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $brand->getName(),
                        'url' => $this->generateUrl('admin_brand_edit', ['id' => $brand->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_brand_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing brand - %name%', ['%name%' => $brand->getName()]));

        return $this->render('@ShopsysAdministration/content/brand/edit.html.twig', [
            'form' => $form->createView(),
            'brand' => $brand,
        ]);
    }

    #[Route(path: '/brand/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()->select('b')->from(Brand::class, 'b');
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'b.id');

        $grid = $this->gridFactory->create('brandList', $dataSource, AdminRoleConstant::ROLE_BRAND);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'b.name', t('Name'), true);

        $grid->addEditActionColumn('admin_brand_edit', ['id' => 'b.id']);
        $grid->addDeleteActionColumn('admin_brand_delete', ['id' => 'b.id'])
            ->setConfirmMessage(
                t('Do you really want to remove this brand? If it is used anywhere it will be unset.'),
            );

        $grid->setTheme('@ShopsysAdministration/content/brand/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $this->render('@ShopsysAdministration/content/brand/list.html.twig', [
            'gridView' => $grid->createView(),
            'domains' => $this->domain->getAdminEnabledDomains(),
        ]);
    }

    #[Route(path: '/brand/new/')]
    #[CanCreate]
    public function newAction(Request $request): RedirectResponse|Response
    {
        $brandData = $this->brandDataFactory->create();

        $form = $this->createForm(BrandFormType::class, $brandData, ['brand' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brand = $this->brandFacade->create($brandData);

            $this
                ->addSuccessFlashTwig(
                    t('Brand <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $brand->getName(),
                        'url' => $this->generateUrl('admin_brand_edit', ['id' => $brand->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_brand_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/brand/new.html.twig', [
            'form' => $form->createView(),
            'domains' => $this->domain->getAll(),
        ]);
    }

    #[Route(path: '/brand/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $fullName = $this->brandFacade->getById($id)->getName();

            $this->brandFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Brand <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (BrandNotFoundException $ex) {
            $this->addErrorFlash(t('Selected brand doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_brand_list');
    }
}
