<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use DateTimeZone;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlStateSubscriber;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterGuesser;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterSetResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterStateSubscriber;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterPrototypesFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearchStateSubscriber;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateFactory;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

/**
 * Builds the state of a datagrid the way the container does, with the subscribers of all the features, real forms
 * and no container — the filters are offered in the order of the Czech alphabet and their dates are picked
 * in the time zone of Prague.
 */
final class DatagridStateFactoryFixture
{
    public const string GRID_ID = 'Product';

    /**
     * @param \Symfony\Component\HttpFoundation\Request|null $request Null creates the state outside a request
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope|null $domainControlScope The domain control of the datagrid, instead of the one its configuration resolves to in the application
     */
    public static function create(
        ?Request $request = null,
        ?DomainControlScope $domainControlScope = null,
    ): DatagridStateFactory {
        $formFactory = self::createFormFactory();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(self::createDomainControlStateSubscriber($domainControlScope));
        $eventDispatcher->addSubscriber(new QuickSearchStateSubscriber(
            new SearchablePathResolver(new ExpressionOperatorEnum()),
            new QuickSearchFormFactory($formFactory),
        ));
        $eventDispatcher->addSubscriber(new FilterStateSubscriber(
            self::createFilterSetResolver(),
            new FilterFormFactory($formFactory),
        ));

        $requestStack = new RequestStack();

        if ($request !== null) {
            $requestStack->push($request);
        }

        return new DatagridStateFactory($eventDispatcher, $requestStack);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|null $request Null creates the state outside a request
     */
    public static function createState(
        Datagrid|DatagridConfig $datagrid,
        ?Request $request = null,
        ?DomainControlScope $domainControlScope = null,
    ): DatagridState {
        return self::create($request, $domainControlScope)->create($datagrid instanceof Datagrid ? $datagrid->getConfig() : $datagrid);
    }

    /**
     * A request carrying the forms of the datagrid named `GRID_ID`.
     *
     * @param array<string, mixed>|null $quickSearchQuery
     * @param array<string, mixed>|null $filterQuery
     */
    public static function createRequest(?array $quickSearchQuery = null, ?array $filterQuery = null): Request
    {
        return new Request(array_filter([
            self::GRID_ID . '_search' => $quickSearchQuery,
            self::GRID_ID . '_filter' => $filterQuery,
        ], static fn (?array $query): bool => $query !== null));
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

    /**
     * The subscriber of the application adding the given scope — the scope is resolved from the session there.
     */
    private static function createDomainControlStateSubscriber(
        ?DomainControlScope $domainControlScope,
    ): DomainControlStateSubscriber {
        return new class($domainControlScope) extends DomainControlStateSubscriber {
            /**
             * The factory of the parent is never called.
             */
            public function __construct(
                private readonly ?DomainControlScope $domainControlScope,
            ) {
            }

            #[Override]
            protected function createScope(DatagridConfig $config): ?DomainControlScope
            {
                return $this->domainControlScope;
            }
        };
    }
}
