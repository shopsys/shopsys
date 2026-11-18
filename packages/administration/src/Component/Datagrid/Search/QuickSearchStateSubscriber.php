<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the quick search to the state of a datagrid with any field declared `searchable`.
 */
class QuickSearchStateSubscriber implements EventSubscriberInterface
{
    protected const string FORM_NAME_SUFFIX = '_search';

    public function __construct(
        protected readonly SearchablePathResolver $searchablePathResolver,
        protected readonly QuickSearchFormFactory $quickSearchFormFactory,
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
        $searchedPaths = $this->searchablePathResolver->resolve($event->config->adapter, $event->config->fields);

        if ($searchedPaths === []) {
            return;
        }

        $formName = $event->config->createFormName(static::FORM_NAME_SUFFIX);
        $query = $event->getQuery($formName);

        $event->addPart(new QuickSearch(
            $formName,
            $query === null ? null : $this->quickSearchFormFactory->getTerm($formName, $query),
            $searchedPaths,
        ));
    }
}
