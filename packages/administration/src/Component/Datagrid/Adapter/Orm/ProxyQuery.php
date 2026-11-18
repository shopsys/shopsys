<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateTimeUtcImmutableType;
use Doctrine\DBAL\Types\DateTimeUtcType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Stringable;

/**
 * Owns the query of a datagrid and adds the selects, the parameters and the `EXISTS` subqueries its paths need.
 */
final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    private const string SUBQUERY_ALIAS_PREFIX = 'dgsub_';

    private const string PARAMETER_NAME_PREFIX = 'dgexpr_';

    /**
     * Doctrine types converting a mutable date only.
     */
    private const array MUTABLE_DATE_TYPES = [
        DateType::class,
        DateTimeType::class,
        DateTimeTzType::class,
        DateTimeUtcType::class,
        TimeType::class,
    ];

    /**
     * Doctrine types converting an immutable date only.
     */
    private const array IMMUTABLE_DATE_TYPES = [
        DateImmutableType::class,
        DateTimeImmutableType::class,
        DateTimeTzImmutableType::class,
        DateTimeUtcImmutableType::class,
        TimeImmutableType::class,
    ];

    /**
     * Doctrine types writing the time as it is, without its zone, and reading it in the default time zone.
     */
    private const array ZONELESS_DATETIME_TYPES = [
        DateTimeType::class,
        DateTimeImmutableType::class,
    ];

    private QueryBuilder $queryBuilder;

    private PathJoiner $pathJoiner;

    /**
     * @var array<string, string>
     */
    private array $selects = [];

    private int $parameterCounter = 0;

    private int $subqueryCounter = 0;

    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly EntityManagerInterface $entityManager,
        private readonly PathWalkerInterface $pathWalker,
        private readonly string $locale,
    ) {
        $this->queryBuilder = $this->createQueryBuilder(self::DEFAULT_ALIAS);
        $this->pathJoiner = new PathJoiner($this->queryBuilder, self::DEFAULT_ALIAS, $locale, correlated: false);
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
        $this->pathJoiner = $this->pathJoiner->withQueryBuilder($this->queryBuilder);
    }

    public function addSelect(string $select): void
    {
        if (array_key_exists($select, $this->selects)) {
            return;
        }

        $pathResolution = $this->resolvePath($select);

        if ($pathResolution->isToMany()) {
            throw UnsupportedConditionException::toManySelectNotSupported($select);
        }

        $this->selects[$select] = $pathResolution->selectAlias;
        $this->queryBuilder->addSelect($pathResolution->dqlExpression . ' AS ' . $pathResolution->selectAlias);
    }

    /**
     * Resolves a dot notation path against the query, joining every association on the way —
     * `currency.code` → `LEFT JOIN App\Currency currency_join WITH o.currency = currency_join`, `currency_join.code`.
     *
     * A path through a to-many association is returned without a DQL expression, match it by `exists()`.
     */
    public function resolvePath(string $path): PathResolution
    {
        return $this->pathJoiner->resolve($this->pathWalker->walk($this->entityClass, $path));
    }

    /**
     * Registers a value of an expression under a name of its own.
     *
     * @param string|null $type Doctrine type the value is converted by, typically the type of the compared field
     * @return string Name of the parameter, to be used as `:name` in the expression
     */
    public function addParameter(mixed $value, ?string $type = null): string
    {
        $name = self::PARAMETER_NAME_PREFIX . $this->parameterCounter++;
        $this->queryBuilder->setParameter($name, $this->normalizeDate($value, $type), $type);

        return $name;
    }

    /**
     * Builds an `EXISTS` subquery matching a record with a related row on the path:
     * `EXISTS(SELECT 1 FROM App\Product dgsub_0 INNER JOIN dgsub_0.domains dgsub_0_domains_join WHERE dgsub_0.id = o.id AND dgsub_0_domains_join.domainId = :dgexpr_0)`.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string $buildPredicate Builds the predicate the related row has to match, from the path resolved in the subquery
     */
    public function exists(string $path, Closure $buildPredicate): string
    {
        $subqueryAlias = self::SUBQUERY_ALIAS_PREFIX . $this->subqueryCounter++;
        $identifierField = $this->entityManager->getClassMetadata($this->entityClass)->getSingleIdentifierFieldName();
        $subqueryBuilder = $this->createQueryBuilder($subqueryAlias)
            ->select('1')
            ->where(sprintf('%s.%s = %s.%s', $subqueryAlias, $identifierField, self::DEFAULT_ALIAS, $identifierField));

        // the joins may narrow the related rows by conditions of their own, added after the correlation
        $subqueryPathJoiner = new PathJoiner($subqueryBuilder, $subqueryAlias, $this->locale, correlated: true);
        $pathResolution = $subqueryPathJoiner->resolve($this->pathWalker->walk($this->entityClass, $path));
        $subqueryBuilder->andWhere($buildPredicate($pathResolution));

        // the subquery is embedded as plain DQL, so the parameters of its own joins have to be carried
        // over to the query that is actually executed
        foreach ($subqueryBuilder->getParameters() as $parameter) {
            $this->queryBuilder->setParameter($parameter->getName(), $parameter->getValue(), $parameter->getType());
        }

        return sprintf('EXISTS(%s)', $subqueryBuilder->getDQL());
    }

    /**
     * Narrows the query by an expression built for it.
     */
    public function applyExpression(Stringable|string $expression): void
    {
        $this->queryBuilder->andWhere($expression);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * Doctrine converts a date only of the class its type declares and writes it as it is, without its time zone,
     * while a condition holds a date of either class in any time zone. The date is converted to the class of the
     * type, and to the default time zone the stored date and time is read in, so it means the same moment.
     */
    private function normalizeDate(mixed $value, ?string $type): mixed
    {
        if ($value instanceof DateTimeInterface === false || $type === null || Type::hasType($type) === false) {
            return $value;
        }

        $doctrineType = Type::getType($type);

        if ($this->isOfAny($doctrineType, self::ZONELESS_DATETIME_TYPES)) {
            $value = DateTimeImmutable::createFromInterface($value)->setTimezone(new DateTimeZone(date_default_timezone_get()));
        }

        if ($this->isOfAny($doctrineType, self::MUTABLE_DATE_TYPES)) {
            return DateTime::createFromInterface($value);
        }

        if ($this->isOfAny($doctrineType, self::IMMUTABLE_DATE_TYPES)) {
            return DateTimeImmutable::createFromInterface($value);
        }

        return $value;
    }

    /**
     * @param class-string[] $classes
     */
    private function isOfAny(object $object, array $classes): bool
    {
        return array_any($classes, static fn (string $class): bool => $object instanceof $class);
    }

    private function createQueryBuilder(string $alias): QueryBuilder
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->entityManager->getRepository($this->entityClass);

        return $repository->createQueryBuilder($alias)->resetDQLPart('select');
    }
}
