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

    /**
     * @param string $constantName
     */
    public function __construct(
        JCallExprNode $callExprNode,
        $constantName
    ) {
        $this->callExprNode = $callExprNode;
        $this->constantName = $constantName;
    }

    /**
     * @return \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode
     */
    public function getCallExprNode()
    {
        return $this->callExprNode;
    }

    public function getConstantName()
    {
        return $this->constantName;
    }
}
