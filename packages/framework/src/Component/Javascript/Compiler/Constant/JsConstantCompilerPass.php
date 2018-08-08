<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant;

use PLUG\JavaScript\JNodes\nonterminal\JProgramNode;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Constant\JsConstantCallParser;

class JsConstantCompilerPass implements JsCompilerPassInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Parser\Constant\JsConstantCallParser
     */
    private $jsConstantCallParser;

    public function __construct(
        JsConstantCallParser $jsConstantCallParser
    ) {
        $this->jsConstantCallParser = $jsConstantCallParser;
    }

    public function process(JProgramNode $node): void
    {
        $jsConstantCalls = $this->jsConstantCallParser->parse($node);

        foreach ($jsConstantCalls as $jsConstantCall) {
            $callExprNode = $jsConstantCall->getCallExprNode();
            $constantName = $jsConstantCall->getConstantName();

            $constantValue = $this->getConstantValue($constantName);
            $constantValueJson = json_encode($constantValue);

            if ($constantValueJson === false) {
                throw new \Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\Exception\CannotConvertToJsonException(
                    'Constant "' . $constantName . '" cannot be converted to JSON'
                );
            }

            $callExprNode->terminate(json_encode($constantValue));
        }
    }

    /**
     * @return mixed
     */
    private function getConstantValue(string $constantName)
    {
        // Normal defined constant (either class or global)
        if (defined($constantName)) {
            return constant($constantName);
        }

        // Special ::class constant
        $constantNameParts = explode('::', $constantName);
        if (count($constantNameParts) === 2 && $constantNameParts[1] === 'class') {
            $className = $constantNameParts[0];

            if (class_exists($className)) {
                // remove leading backslash to be consistent with behavior of ::class in PHP
                return ltrim($className, '\\');
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Javascript\Compiler\Constant\Exception\ConstantNotFoundException(
            'Constant "' . $constantName . '" not defined in PHP code'
        );
    }
}
