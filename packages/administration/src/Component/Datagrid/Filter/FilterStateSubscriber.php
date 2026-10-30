<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the filter to the state of a datagrid offering any filter — those picked for the fields declared `filterable`
 * and the declared ones.
 *
 * The filters are validated even outside a request, so that a declaration that cannot work fails in a test
 * or a command as well; only the form needs the request. The form is submitted here, once — the records are narrowed
 * by it before they are loaded, and the component renders the very same form with its errors.
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

    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function onDatagridState(DatagridStateEvent $event): void
    {
        $filters = $this->filterSetResolver->resolve($event->config->adapter, $event->config->fields, $event->config->filters);

        if ($filters->isEmpty() || $event->isInRequest() === false) {
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
