<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Action\AbstractAction;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\SalesRepresentative\SalesRepresentativeCrudHandler;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Form\Admin\SalesRepresentative\SalesRepresentativeFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneNumberSearchHelper;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use SortDirection;

#[CrudController(SalesRepresentative::class)]
#[ForRole(AdminRoleConstant::ROLE_SALES_REPRESENTATIVE)]
class SalesRepresentativeController extends AbstractCrudController
{
    protected const int DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT = 10;

    public function __construct(
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuSection(SideMenuBuilder::ROOT_CUSTOMER, null, ['after' => SideMenuBuilder::LIST_CUSTOMER])
            ->registerHandler(SalesRepresentativeCrudHandler::class);
    }

    #[Override]
    protected function configureActions(ActionsConfig $actions): void
    {
        $actions->update(
            ActionType::LIST,
            ActionType::CREATE->value,
            static fn (AbstractAction $action): AbstractAction => $action->setLabel(t('Create new sales representative')),
        );
    }

    #[Override]
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->addSelect(ExtendedClassNameResolver::resolve(PhoneNumberSearchHelper::class)::getDqlExpression('o') . ' AS telephone');
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $labelsIndexedBySalesRepresentativeId = null;
        $customerUsersSummariesIndexedBySalesRepresentativeId = null;

        $datagrid
            ->add('lastName', [
                'label' => t('Last name'),
            ])
            ->add('firstName', [
                'label' => t('First name'),
            ])
            ->add('email', [
                'label' => t('E-mail'),
            ])
            ->add('telephone', [
                'label' => t('Telephone'),
                'virtual' => true,
                'property' => 'telephone',
                'sortable' => false,
            ])
            ->add('label', [
                'visible' => false,
                'virtual' => true,
                'transform' => function (mixed $value, array $row, array $results) use (&$labelsIndexedBySalesRepresentativeId): ?string {
                    if ($labelsIndexedBySalesRepresentativeId === null) {
                        $labelsIndexedBySalesRepresentativeId = [];
                        $salesRepresentatives = $this->salesRepresentativeFacade->getByIds(array_column($results, 'id'));

                        foreach ($salesRepresentatives as $salesRepresentative) {
                            $labelsIndexedBySalesRepresentativeId[$salesRepresentative->getId()] = $salesRepresentative->toHumanReadable();
                        }
                    }

                    return $labelsIndexedBySalesRepresentativeId[$row['id']] ?? null;
                },
            ])
            ->add('customerUsersSummary', [
                'visible' => false,
                'virtual' => true,
                'transform' => function (mixed $value, array $row, array $results) use (&$customerUsersSummariesIndexedBySalesRepresentativeId): ?array {
                    if ($customerUsersSummariesIndexedBySalesRepresentativeId === null) {
                        $customerUsersSummariesIndexedBySalesRepresentativeId = $this->customerUserFacade->getCustomerUserEmailsSummaryIndexedBySalesRepresentativeId(
                            array_map(intval(...), array_column($results, 'id')),
                            static::DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT,
                        );
                    }

                    return $customerUsersSummariesIndexedBySalesRepresentativeId[$row['id']] ?? null;
                },
            ]);

        $datagrid->setDefaultOrder('lastName', SortDirection::Ascending);

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->addCallback(
                function (array $row, RowAction $action): void {
                    $action->setConfirmMessage($this->getDeleteConfirmMessage($row));
                },
            ),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
        $formConfigurator->useFormType(SalesRepresentativeFormType::class, [
            'salesRepresentative' => $entity,
        ]);
    }

    protected function getDeleteConfirmMessage(array $row): string
    {
        $label = htmlspecialchars($row['label']);
        $customersCount = $row['customerUsersSummary']['count'];
        $customersEnumeration = implode('<br>', array_map(htmlspecialchars(...), $row['customerUsersSummary']['emails']));

        if ($customersCount === 0) {
            return t(
                'Do you really want to remove sales representative "%label%" permanently? It is not used anywhere.',
                ['%label%' => $label],
            );
        }

        return t(
            '{1}Sales representative "%label%" is assigned to %count% customer and will be removed if you proceed.<br><br>
                        Customer:<br>
                        %customersEnumeration%<br><br>
                        Do you really want to remove sales representative "%label%" permanently?
                        |[2,10]Sales representative "%label%" is assigned to %count% customers and will be removed if you proceed.<br><br>
                        Customers:<br>
                        %customersEnumeration%<br><br>
                        Do you really want to remove sales representative "%label%" permanently?
                        |[11,Inf]Sales representative "%label%" is assigned to %count% customers and will be removed if you proceed.<br><br>
                        Customers:<br>
                        %customersEnumeration%<br>
                        +%extraCount% more<br><br>
                        Do you really want to remove sales representative "%label%" permanently?',
            [
                '%label%' => $label,
                '%count%' => $customersCount,
                '%extraCount%' => $customersCount - static::DISPLAYED_CUSTOMERS_WHILE_DELETING_SALES_REPRESENTATIVE_COUNT,
                '%customersEnumeration%' => $customersEnumeration,
            ],
        );
    }
}
