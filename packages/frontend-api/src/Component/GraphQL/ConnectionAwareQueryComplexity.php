<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\GraphQL;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\SelectionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use GraphQL\Validator\Rules\QueryComplexity;
use Override;

class ConnectionAwareQueryComplexity extends QueryComplexity
{
    public function __construct(int $maxQueryComplexity)
    {
        parent::__construct($maxQueryComplexity);

        $this->name = QueryComplexity::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function nodeComplexity(SelectionNode $node): int
    {
        // __typename is scored 0 as its easy to resolve graphql metadata often used for caching
        if ($node instanceof FieldNode && $node->name->value === Introspection::TYPE_NAME_FIELD_NAME) {
            return 0;
        }

        if ($this->shouldUseConnectionFieldComplexity($node)) {
            return $this->connectionFieldComplexity($node);
        }

        return parent::nodeComplexity($node);
    }

    protected function shouldUseConnectionFieldComplexity(SelectionNode $node): bool
    {
        if (!$node instanceof FieldNode) {
            return false;
        }

        $fieldDefinition = $this->fieldDefinition($node);

        if ($fieldDefinition === null) {
            return false;
        }

        // Custom complexity overrides the default connection complexity calculation.
        if ($fieldDefinition->complexityFn !== null) {
            return false;
        }

        $namedType = Type::getNamedType($fieldDefinition->getType());

        return $namedType !== null && str_ends_with($namedType->name(), 'Connection');
    }

    protected function connectionFieldComplexity(SelectionNode $node): int
    {
        if (
            !$node instanceof FieldNode
            || $node->name->value === Introspection::SCHEMA_FIELD_NAME
            || $this->directiveExcludesField($node)
            || $node->selectionSet === null
        ) {
            return 0;
        }

        $selectionComplexity = $this->splitConnectionSelectionComplexity($node->selectionSet);
        $arguments = $this->buildFieldArguments($node);

        return $selectionComplexity['metadata']
            + $this->getRequestedConnectionItemsCount($arguments) * ($selectionComplexity['items'] + 1);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    protected function getRequestedConnectionItemsCount(array $arguments): int
    {
        if (isset($arguments['first'])) {
            return (int)$arguments['first'];
        }

        if (isset($arguments['last'])) {
            return (int)$arguments['last'];
        }

        return 10;
    }

    /**
     * @return array{metadata: int, items: int}
     */
    protected function splitConnectionSelectionComplexity(SelectionSetNode $selectionSet): array
    {
        $metadataComplexity = 0;
        $itemsComplexity = 0;

        foreach ($this->getExecutableFieldSelections($selectionSet) as $selection) {
            if (in_array($selection->name->value, ['edges', 'nodes'], true)) {
                $itemsComplexity += $this->itemFieldComplexity($selection);

                continue;
            }

            $metadataComplexity += $this->nodeComplexity($selection);
        }

        return [
            'metadata' => $metadataComplexity,
            'items' => $itemsComplexity,
        ];
    }

    protected function itemFieldComplexity(FieldNode $node): int
    {
        if ($node->selectionSet === null) {
            return $this->nodeComplexity($node);
        }

        if ($node->name->value === 'nodes') {
            return $this->fieldComplexity($node->selectionSet);
        }

        return $this->edgeSelectionComplexity($node->selectionSet);
    }

    protected function edgeSelectionComplexity(SelectionSetNode $selectionSet): int
    {
        $complexity = 0;

        foreach ($this->getExecutableFieldSelections($selectionSet) as $selection) {
            if ($selection->name->value === 'node' && $selection->selectionSet !== null) {
                $complexity += $this->fieldComplexity($selection->selectionSet);

                continue;
            }

            $complexity += $this->nodeComplexity($selection);
        }

        return $complexity;
    }

    /**
     * @return iterable<\GraphQL\Language\AST\FieldNode>
     */
    protected function getExecutableFieldSelections(SelectionSetNode $selectionSet): iterable
    {
        foreach ($selectionSet->selections as $selection) {
            if ($selection instanceof FieldNode) {
                if (
                    $selection->name->value === Introspection::TYPE_NAME_FIELD_NAME
                    || $this->directiveExcludesField($selection)
                ) {
                    continue;
                }

                yield $selection;

                continue;
            }

            if ($selection instanceof InlineFragmentNode) {
                yield from $this->getExecutableFieldSelections($selection->selectionSet);

                continue;
            }

            if (!($selection instanceof FragmentSpreadNode)) {
                continue;
            }

            $fragment = $this->getFragment($selection);

            if ($fragment === null) {
                continue;
            }

            yield from $this->getExecutableFieldSelections($fragment->selectionSet);
        }
    }
}
