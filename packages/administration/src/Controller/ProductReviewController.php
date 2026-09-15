<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Security\Role\AdminRoleSectionsProvider;
use Shopsys\AdministrationBundle\Model\ProductReview\ProductReviewEditHandler;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Form\Admin\ProductReview\ProductReviewFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewFacade;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use SortDirection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[CrudController(ProductReview::class)]
class ProductReviewController extends AbstractCrudController
{
    public function __construct(
        protected readonly EntityLogFacade $entityLogFacade,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
        protected readonly ProductReviewFacade $productReviewFacade,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $enabledDomainIds = $this->productReviewEnabledChecker->getEnabledDomainIds();

        $config
            ->setMenuSection(SideMenuBuilder::ROOT_PRODUCT, null, ['after' => SideMenuBuilder::LIST_PRODUCT])
            ->setListDomainControl(CrudListDomainControl::QUICK_FILTER, $enabledDomainIds)
            ->setCustomRoleSection(AdminRoleSectionsProvider::PRODUCTS_CATALOG)
            ->registerHandler(ProductReviewEditHandler::class)
            ->disable(!$this->productReviewEnabledChecker->isEnabledOnAnyDomain());
    }

    #[Override]
    protected function configureActions(ActionsConfig $actions): void
    {
        $actions->add(
            ActionType::EDIT,
            Action::create('approveReview', t('Approve review'))
                ->setIcon('checked')
                ->setAttribute('class', 'btn-success', true)
                ->displayIf(fn (ProductReview $productReview) => $productReview->getStatus() === ProductReviewStatusEnum::STATUS_PENDING)
                ->linkToRoute('admin_crud_product_review_approve', fn (ProductReview $productReview) => [
                    'id' => $productReview->getId(),
                ]),
        );
    }

    #[Override]
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->addSelect('CASE WHEN o.text IS NULL OR o.text = \'\' THEN false ELSE true END as hasTextReview');
        $queryBuilder->addSelect('CASE WHEN o.status = :pendingStatus THEN 2 WHEN o.status = :approvedStatus THEN 1 ELSE 0 END AS statusPriority');
        $queryBuilder->addOrderBy('statusPriority', SortDirection::Descending);
        $queryBuilder->addOrderBy('createdAt', SortDirection::Descending);

        $queryBuilder
            ->setParameter('pendingStatus', ProductReviewStatusEnum::STATUS_PENDING)
            ->setParameter('approvedStatus', ProductReviewStatusEnum::STATUS_APPROVED);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('createdAt', [
                'label' => t('Date and time'),
            ])
            ->add('productName', [
                'label' => t('Product'),
                'template' => '@ShopsysAdministration/content/productReview/grid/productName.html.twig',
            ])
            ->add('productId', [
                'visible' => false,
                'property' => 'product.id',
            ])
            ->add('customerUserId', [
                'visible' => false,
                'property' => 'customerUser.id',
            ])
            ->add('catnum', [
                'visible' => false,
            ])
            ->add('firstName', [
                'visible' => false,
            ])
            ->add('lastName', [
                'label' => t('Reviewer'),
                'template' => '@ShopsysAdministration/content/productReview/grid/reviewer.html.twig',
            ])
            ->add('rating', [
                'label' => t('Rating'),
                'template' => '@ShopsysAdministration/content/productReview/grid/rating.html.twig',
            ])
            ->add('isVerifiedPurchase', [
                'label' => t('Verified purchase'),
            ])
            ->add('statusValue', [
                'visible' => false,
                'property' => 'status',
            ])
            ->add('status', [
                'label' => t('Status'),
                'template' => '@ShopsysAdministration/content/productReview/grid/status.html.twig',
                'virtual' => true,
                'property' => 'statusPriority',
            ])
            ->add('text', [
                'label' => t('Text review'),
                'virtual' => true,
                'property' => 'hasTextReview',
            ]);

        if ($this->domain->isMultidomain()) {
            $datagrid->add('domainId', [
                'label' => t('Domain'),
            ]);
        }
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(ProductReviewFormType::class, [
            'productReview' => $entity,
        ]);
    }

    #[Override]
    protected function getEditTemplate(): string
    {
        return '@ShopsysAdministration/content/productReview/edit.html.twig';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getEditViewData(object $entity): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview $productReview */
        $productReview = $entity;

        return [
            'entityLogEntityName' => $this->entityLogFacade->getEntityNameByEntity(ProductReview::class),
            'productReview' => $productReview,
        ];
    }

    #[Route(path: '/product-review/approve/{id}', name: 'admin_crud_product_review_approve', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[CanEdit]
    #[CsrfProtection]
    public function approveAction(int $id): Response
    {
        /** @var \Shopsys\AdministrationBundle\Model\ProductReview\ProductReviewEditHandler $handler */
        $handler = $this->definition->getHandlerForAction(ActionType::EDIT);
        $productReview = $handler->getById($id);

        if ($productReview->getStatus() !== ProductReviewStatusEnum::STATUS_PENDING) {
            $this->addErrorFlashTwig(
                t('Review <strong><a href="{{ url }}">{{ reviewName }}</a></strong> is no longer pending, so it cannot be approved.'),
                [
                    'reviewName' => $productReview->toHumanReadable(),
                    'url' => $this->generateUrl('admin_crud_product_review_edit', ['id' => $id]),
                ],
            );

            return $this->redirectToRoute('admin_crud_product_review_list');
        }

        $this->productReviewFacade->approve($productReview);

        $this->addSuccessFlashTwig(
            t('Review <strong><a href="{{ url }}">{{ reviewName }}</a></strong> was approved.'),
            [
                'reviewName' => $productReview->toHumanReadable(),
                'url' => $this->generateUrl('admin_crud_product_review_edit', ['id' => $id]),
            ],
        );

        return $this->redirectToRoute('admin_crud_product_review_list');
    }
}
