<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowingResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;

/**
 * Builds the narrowing of a datagrid the way the container does, with real forms and no container.
 */
final class DatagridNarrowingResolverFactory
{
    public const string GRID_ID = 'Product';

    public static function create(): DatagridNarrowingResolver
    {
        return new DatagridNarrowingResolver(
            new SearchablePathResolver(new ExpressionOperatorEnum()),
            new DatagridFormFactory(self::createFormFactory()),
        );
    }

    /**
     * @param array<string, mixed>|null $quickSearchQuery
     */
    public static function createRequestState(?array $quickSearchQuery = null): DatagridRequestState
    {
        return new DatagridRequestState(self::GRID_ID, $quickSearchQuery);
    }

    public static function createFormFactory(): FormFactoryInterface
    {
        return Forms::createFormFactory();
    }
}
