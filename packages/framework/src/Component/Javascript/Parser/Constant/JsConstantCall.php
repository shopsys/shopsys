<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser\Constant;

use PLUG\JavaScript\JNodes\nonterminal\JCallExprNode;

class JsConstantCall
{
    /**
     * @var \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode
     */
    private $callExprNode;

    /**
     * @var string
     */
    private $constantName;
    
    public function __construct(
        JCallExprNode $callExprNode,
        string $constantName
    ) {
        $this->callExprNode = $callExprNode;
        $this->constantName = $constantName;
    }

    public function getCallExprNode(): \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode
    {
        return $this->callExprNode;
    }

    public function getConstantName(): string
    {
        return $this->constantName;
    }
}
