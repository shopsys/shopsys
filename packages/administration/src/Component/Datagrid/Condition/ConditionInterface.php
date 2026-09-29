<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * A node of the tree saying which records a datagrid lists — plain data, independent of the medium the
 * records come from.
 *
 * The tree is built by whatever narrows the datagrid (the domain control, a search, a filter), handed to
 * the adapter as a part of `DatasourceRequest`, and only there compiled into the medium by
 * `ExpressionCompilerInterface` with the `ExpressionBuilderInterface` of that adapter. Being data, it can
 * be composed, inspected, serialized and asserted on without any medium at hand.
 *
 * The vocabulary consists of `Comparison`, `Composite` and `Negation`. A medium says what the vocabulary
 * cannot through a `MediumSpecificConditionInterface`, which only that medium compiles.
 */
interface ConditionInterface
{
}
