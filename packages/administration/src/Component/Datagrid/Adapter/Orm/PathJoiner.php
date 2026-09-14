<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LogicException;

/**
 * Joins what a walked path needs into one query and tells how the value of the path is addressed there. Every join
 * is added once, under an alias derived from the path, so the selects and the conditions of the query share it.
 *
 * @internal this class is not intended to be used directly by other developers
 */
final class PathJoiner
{
    /**
     * Aliases of the joins already added, keyed by themselves.
     *
     * @var array<string, string>
     */
    private array $joins = [];

    public function __construct(
        private QueryBuilder $queryBuilder,
        private readonly string $rootAlias,
        private readonly string $locale,
        private readonly JoinStyle $joinStyle,
    ) {
    }

    /**
     * The same joins for a copy of the query, which already holds them.
     */
    public function withQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $pathJoiner = clone $this;
        $pathJoiner->queryBuilder = $queryBuilder;

        return $pathJoiner;
    }

    /**
     * A path through a collection is resolved without a DQL expression when the query does not join collections,
     * so that it is matched by a correlated subquery instead.
     */
    public function resolve(WalkedPath $walkedPath): PathResolution
    {
        $description = $walkedPath->description;
        $selectAlias = $this->getAlias($description->path);

        if ($description->isToMany() && $this->joinStyle->joinsCollections() === false) {
            return new PathResolution(null, $selectAlias, null, $description);
        }

        $alias = $this->rootAlias;
        $terminalStep = null;

        foreach ($walkedPath->steps as $step) {
            if ($step->kind === PathStepKind::TRANSLATIONS) {
                $alias = $this->joinTranslations($alias);
            } elseif ($step->kind === PathStepKind::ASSOCIATION) {
                $alias = $this->joinAssociation($step, $alias);
            } else {
                $terminalStep = $step;
            }
        }

        $dqlExpression = match ($terminalStep?->kind) {
            PathStepKind::FIELD => $alias . '.' . $terminalStep->field,
            PathStepKind::TRANSLATED_FIELD => $this->joinTranslations($alias) . '.' . $terminalStep->field,
            PathStepKind::IDENTITY => sprintf('IDENTITY(%s.%s)', $alias, $terminalStep->field),
            // the path ends with an association, addressed by the alias of its join
            null => $alias,
            default => throw new LogicException(sprintf('Step "%s" cannot end a path.', $terminalStep->kind->name)),
        };

        return new PathResolution($dqlExpression, $selectAlias, $terminalStep?->fieldType, $description);
    }

    private function getAlias(string $path): string
    {
        return strtr($path, ['.' => '__']);
    }

    /**
     * @return string Alias of the joined association
     */
    private function joinAssociation(PathStep $step, string $currentAlias): string
    {
        $joinAlias = $this->getAlias($step->partPath) . '_join';

        if ($this->joinStyle->prefixesAliases()) {
            $joinAlias = $this->rootAlias . '_' . $joinAlias;
        }

        if (array_key_exists($joinAlias, $this->joins) === false) {
            $this->joins[$joinAlias] = $joinAlias;
            $this->joinStyle->join($this->queryBuilder, $currentAlias, $step, $joinAlias);
        }

        return $joinAlias;
    }

    /**
     * @return string Alias of the joined translations of the given alias
     */
    private function joinTranslations(string $currentAlias): string
    {
        $joinAlias = $currentAlias . '_tr';

        if (array_key_exists($joinAlias, $this->joins) === false) {
            $this->joins[$joinAlias] = $joinAlias;
            $this->queryBuilder->leftJoin(
                $currentAlias . '.' . PathWalker::TRANSLATIONS_ASSOCIATION,
                $joinAlias,
                Join::WITH,
                sprintf('%1$s.locale = :%1$s_locale', $joinAlias),
            );
            $this->queryBuilder->setParameter($joinAlias . '_locale', $this->locale);
        }

        return $joinAlias;
    }
}
