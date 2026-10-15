<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use DateTimeZone;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterGuesser;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterSetResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterPrototypesFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Narrowing\DatagridNarrowingResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * Builds the narrowing of a datagrid the way the container does, with real forms and no container — the filters
 * are offered in the order of the Czech alphabet and their dates are picked in the time zone of Prague.
 */
final class DatagridNarrowingResolverFactory
{
    public const string GRID_ID = 'Product';

    public static function create(): DatagridNarrowingResolver
    {
        return new DatagridNarrowingResolver(
            new SearchablePathResolver(new ExpressionOperatorEnum()),
            self::createFilterSetResolver(),
            new DatagridFormFactory(self::createFormFactory()),
        );
    }

    public static function createFilterSetResolver(): FilterSetResolver
    {
        $displayTimeZoneProvider = new class() implements DisplayTimeZoneProviderInterface {
            #[Override]
            public function getDisplayTimeZoneByDomainId(int $domainId): DateTimeZone
            {
                return $this->getDisplayTimeZoneForAdmin();
            }

            #[Override]
            public function getDisplayTimeZoneForAdmin(): DateTimeZone
            {
                return new DateTimeZone('Europe/Prague');
            }
        };

        $administratorLocalizationFacade = new class() extends AdministratorLocalizationFacade {
            public function __construct()
            {
            }

            #[Override]
            public function getCurrentAdminLocaleOrDefault(): string
            {
                return 'cs';
            }
        };

        return new FilterSetResolver(new FilterGuesser(), new ExpressionOperatorEnum(), $displayTimeZoneProvider, $administratorLocalizationFacade);
    }

    /**
     * @param array<string, mixed>|null $quickSearchQuery
     * @param array<string, mixed>|null $filterQuery
     */
    public static function createRequestState(
        ?array $quickSearchQuery = null,
        ?array $filterQuery = null,
    ): DatagridRequestState {
        return new DatagridRequestState(self::GRID_ID, $quickSearchQuery, $filterQuery);
    }

    /**
     * The forms of the filter are validated as in the application, so the validator extension is registered.
     */
    public static function createFormFactory(): FormFactoryInterface
    {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType(new FilterRuleType())
            ->addType(new DatagridFilterFormType())
            ->addType(new FilterPrototypesFormType())
            ->getFormFactory();
    }
}
