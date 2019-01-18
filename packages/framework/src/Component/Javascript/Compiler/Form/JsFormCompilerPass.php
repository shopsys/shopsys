<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form;

use PLUG\JavaScript\JNodes\JNodeBase;
use PLUG\JavaScript\JNodes\nonterminal\JProgramNode;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form\Exception\BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form\Exception\CannotParseFormTypeException;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\Form\Exception\FormTypeNotProvidedException;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\UnsupportedNodeException;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\JsFunctionCallParser;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\JsStringParser;
use Symfony\Component\Form\FormTypeInterface;

class JsFormCompilerPass implements JsCompilerPassInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Parser\JsFunctionCallParser
     */
    private $jsFunctionCallParser;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Parser\JsStringParser
     */
    private $jsStringParser;

    /**
     * @var \Symfony\Component\Form\Extension\Core\Type\FormType[]
     */
    private $knownFormTypes;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Javascript\Parser\JsFunctionCallParser $jsFunctionCallParser
     * @param \Shopsys\FrameworkBundle\Component\Javascript\Parser\JsStringParser $jsStringParser
     * @param \Symfony\Component\Form\Extension\Core\Type\FormType[] $knownFormTypes
     */
    public function __construct(
        JsFunctionCallParser $jsFunctionCallParser,
        JsStringParser $jsStringParser,
        iterable $knownFormTypes
    ) {
        $this->jsFunctionCallParser = $jsFunctionCallParser;
        $this->jsStringParser = $jsStringParser;
        $this->knownFormTypes = $knownFormTypes;
    }

    /**
     * @param \PLUG\JavaScript\JNodes\nonterminal\JProgramNode $node
     */
    public function process(JProgramNode $node)
    {
        $callExprNodes = $node->get_nodes_by_symbol(J_CALL_EXPR);

        /** @var \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode[] $callExprNodes */
        foreach ($callExprNodes as $callExprNode) {
            if ($this->jsFunctionCallParser->getFunctionName($callExprNode) === 'Shopsys.allFormsOfType') {
                $argumentNodes = $this->jsFunctionCallParser->getArgumentNodes($callExprNode);
                if (!isset($argumentNodes[0])) {
                    throw new BadMethodCallException('JS method "Shopsys.allFormsOfType" needs to be called with a single argument.');
                }

                $formType = $this->parseFormType($argumentNodes[0]);

                $allOfType = $this->getAllOfType($formType);

                $callExprNode->terminate(json_encode($allOfType));
            }
        }
    }

    /**
     * @param \PLUG\JavaScript\JNodes\JNodeBase $node
     * @return string
     */
    private function parseFormType(JNodeBase $node): string
    {
        try {
            $formType = $this->jsStringParser->getConcatenatedString($node);
        } catch (UnsupportedNodeException $e) {
            throw new CannotParseFormTypeException('A FormType class name must be provided as a literal string.', $e);
        }
        if (!class_exists($formType)) {
            throw new CannotParseFormTypeException(sprintf('The provided class "%s" cannot be found.', $formType));
        }
        if (!is_a($formType, FormTypeInterface::class, true)) {
            throw new CannotParseFormTypeException(sprintf('The provided class "%s" is not a FormType.', $formType));
        }

        return $formType;
    }

    /**
     * @return string[]
     */
    private function getAllOfType(string $formType): array
    {
        $formTypes = [$formType];
        $childTypes = $this->getChildTypes($formType);
        foreach ($childTypes as $childType) {
            $formTypes = array_merge($formTypes, $this->getAllOfType($childType));
        }

        return $formTypes;
    }

    /**
     * @return string[]
     */
    private function getChildTypes(string $parentType): array
    {
        $childTypes = [];
        foreach ($this->knownFormTypes as $formType) {
            if ($formType->getParent() === $parentType) {
                $childTypes[] = get_class($formType);
            }
        }

        return $childTypes;
    }
}
