<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use Twig\Environment;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * Normalizes Twig translation filters by replacing custom filter "transHtml" by the default filter
 * names "trans" and "transChoice". This ensures that they will be treated the same way by following Twig node visitors.
 *
 * Used for dumping translation messages in both custom and default translation filters because the extractor class
 * \JMS\TranslationBundle\Translation\Extractor\File\TwigFileExtractor is not very extensible.
 */
class CustomTransFiltersVisitor extends AbstractNodeVisitor
{
    protected const CUSTOM_TO_DEFAULT_TRANS_FILTERS_MAP = [
        'transHtml' => 'trans',
    ];
    protected const PRIORITY = -1;

    /**
     * {@inheritdoc}
     *
     * @param \Twig\Node\Node $node
     */
    protected function doEnterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof FilterExpression) {
            $filterNameConstantNode = $node->getNode('filter');
            $filterName = $filterNameConstantNode->getAttribute('value');
            if (array_key_exists($filterName, static::CUSTOM_TO_DEFAULT_TRANS_FILTERS_MAP)) {
                $newFilterName = static::CUSTOM_TO_DEFAULT_TRANS_FILTERS_MAP[$filterName];
                $this->replaceCustomFilterName($node, $newFilterName);
            }
        }

        return $node;
    }

    /**
     * @param \Twig\Node\Expression\FilterExpression $filterExpressionNode
     * @param string $newFilterName
     */
    protected function replaceCustomFilterName(FilterExpression $filterExpressionNode, string $newFilterName): void
    {
        $filterNameConstantNode = $filterExpressionNode->getNode('filter');
        $filterNameConstantNode->setAttribute('value', $newFilterName);

        // \Twig_Node_Expression_Filter has "name" attribute only if it is compiled
        if ($filterExpressionNode->hasAttribute('name')) {
            $filterExpressionNode->setAttribute('name', $newFilterName);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Twig\Node\Node $node
     */
    protected function doLeaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return static::PRIORITY;
    }
}
