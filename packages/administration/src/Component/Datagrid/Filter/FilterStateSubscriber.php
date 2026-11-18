<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the filter to the state of a datagrid offering any filter, either picked for a field declared `filterable`
 * or declared.
 *
 * The form is submitted here, once, as the records are narrowed by it before they are loaded.
 */
class FilterStateSubscriber implements EventSubscriberInterface
{
    protected const string FORM_NAME_SUFFIX = '_filter';

    public function __construct(
        protected readonly FilterSetResolver $filterSetResolver,
        protected readonly FilterFormFactory $filterFormFactory,
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
        $filters = $this->filterSetResolver->resolve($event->config->adapter, $event->config->filters);

        if ($filters->isEmpty()) {
            return;
        }

        $formName = $event->config->createFormName(static::FORM_NAME_SUFFIX);
        $filterPanel = new FilterPanel(
            $this->filterFormFactory->createForm($filters, $formName, $event->getQuery($formName)),
            $filters,
        );
        $event->addPart($filterPanel);

        if ($filterPanel->isInvalid()) {
            $event->markInvalid();
        }
    }
}
