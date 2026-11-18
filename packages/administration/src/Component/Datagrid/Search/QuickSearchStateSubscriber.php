<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds the quick search to the state of a datagrid with any field declared `searchable`.
 *
 * The searchable fields are validated even outside a request, so that a declaration that cannot work fails
 * in a test or a command as well; only the form needs the request.
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

    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     */
    public function onDatagridState(DatagridStateEvent $event): void
    {
        $labelsByPath = $this->searchablePathResolver->resolve($event->config->adapter, $event->config->fields);

        if ($labelsByPath === [] || $event->isInRequest() === false) {
            return;
        }

        $formName = $event->config->createFormName(static::FORM_NAME_SUFFIX);
        $query = $event->getQuery($formName);

        $event->addPart(new QuickSearch(
            $formName,
            $query === null ? null : $this->quickSearchFormFactory->getTerm($formName, $query),
            $labelsByPath,
        ));
    }
}
