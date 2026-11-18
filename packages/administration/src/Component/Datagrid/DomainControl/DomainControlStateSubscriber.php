<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\DomainControl;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the domain control to the state of a datagrid. It limits the records to the domains the datagrid works with.
 * When the administrator works with several domains at once, it displays the domain of every record as well.
 *
 * The scope reads the domains of the logged-in administrator and the selection remembered in the session. The state
 * of a datagrid with a domain control can therefore be created only in a request of a logged-in administrator.
 */
class DomainControlStateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly DomainControlScopeFactory $domainControlScopeFactory,
    ) {
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            DatagridStateEvent::class => 'onDatagridState',
        ];
    }

    public function onDatagridState(DatagridStateEvent $event): void
    {
        $scope = $this->createScope($event->config);

        if ($scope === null) {
            return;
        }

        $event->addPart($scope);

        if ($this->isDomainWorthDisplaying($scope, $event->config)) {
            $event->addField(new FieldDescriptor($scope->domainIdPath, [
                'label' => t('Domain'),
                'template' => '@ShopsysAdministration/datagrid/cell/domain_icon.html.twig',
            ]));
        }
    }

    protected function createScope(DatagridConfig $config): ?DomainControlScope
    {
        return $this->domainControlScopeFactory->create(
            $config->domainControlConfig,
            $config->adapter instanceof EntityClassAwareAdapterInterface ? $config->adapter->getEntityClass() : null,
        );
    }

    /**
     * The domain of a record is not worth displaying:
     *
     * - when the listed entity does not belong to a domain;
     * - when the whole datagrid works with a single domain anyway — a single domain is available, or the domain
     *   switcher selects one;
     * - for a record related to several domains (the path leads through a collection), it has no single domain.
     *
     * Displaying the domain and limiting the records to the domains of the control are independent. Hiding
     * or removing the field never widens the listed records.
     *
     * @phpstan-assert-if-true !null $scope->domainIdPath
     */
    protected function isDomainWorthDisplaying(DomainControlScope $scope, DatagridConfig $config): bool
    {
        if ($scope->domainIdPath === null || $scope->isFilter() === false || $scope->offersChoice() === false) {
            return false;
        }

        return $config->adapter instanceof PathDescribingAdapterInterface === false
            || $config->adapter->describePath($scope->domainIdPath)->isToOne();
    }
}
