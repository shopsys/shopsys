<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator;

use PLUG\JavaScript\JNodes\JNodeBase;
use PLUG\JavaScript\JNodes\nonterminal\JCallExprNode;

class JsTranslatorCall
{
    /**
     * @var \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode
     */
    private $callExprNode;

    /**
     * @var \PLUG\JavaScript\JNodes\JNodeBase
     */
    private $messageIdArgumentNode;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $functionName;

    /**
     * @param string $messageId
     * @param string $domain
     * @param string $functionName
     */
    public function __construct(
        JCallExprNode $callExprNode,
        JNodeBase $messageIdArgumentNode,
        $messageId,
        $domain,
        $functionName
    ) {
        $this->callExprNode = $callExprNode;
        $this->messageIdArgumentNode = $messageIdArgumentNode;
        $this->messageId = $messageId;
        $this->domain = $domain;
        $this->functionName = $functionName;
    }

    public function getCallExprNode(): \PLUG\JavaScript\JNodes\nonterminal\JCallExprNode
    {
        return $this->callExprNode;
    }

    public function getMessageIdArgumentNode(): \PLUG\JavaScript\JNodes\JNodeBase
    {
        return $this->messageIdArgumentNode;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }
}
