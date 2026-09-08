<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Search\Exception\QuickSearchNotConfiguredException;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;

final class QuickSearchApplier
{
    private const string SEARCH_TEXT_PARAMETER_NAME = 'crudQuickSearchText';

    public function __construct(
        private readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function apply(QuickSearchDefinition $quickSearchDefinition, ProxyQuery $proxyQuery, string $searchText): void
    {
        $queryBuilder = $proxyQuery->getQueryBuilder();
        $queryCallback = $quickSearchDefinition->getQueryCallback();

        if ($queryCallback !== null) {
            $queryCallback($queryBuilder, $searchText);

            return;
        }

        $fields = $quickSearchDefinition->getFields();

        if ($fields === []) {
            throw QuickSearchNotConfiguredException::noFieldsAndNoCallback();
        }

        $conditions = [];

        foreach ($fields as $fieldPath) {
            $conditions[] = sprintf(
                'NORMALIZED(%s) LIKE NORMALIZED(:%s)',
                $proxyQuery->getFieldExpression($fieldPath),
                self::SEARCH_TEXT_PARAMETER_NAME,
            );
        }

        $queryBuilder
            ->andWhere('(' . implode(' OR ', $conditions) . ')')
            ->setParameter(
                self::SEARCH_TEXT_PARAMETER_NAME,
                $this->databaseSearchingHelper->getFullTextLikeSearchString($searchText),
            );
    }
}
