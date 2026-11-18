<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * A node of the tree saying which records a datagrid lists, independent of the adapter the records come from.
 * It can be composed, inspected and asserted on without any adapter.
 *
 * The vocabulary consists of `Comparison`, `Composite` and `Negation`.
 * A node outside the vocabulary is compiled only by a builder that knows it.
 */
interface ConditionInterface
{
}
