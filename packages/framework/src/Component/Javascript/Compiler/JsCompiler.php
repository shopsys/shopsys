<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler;

use PLUG\JavaScript\JParser;
use PLUG\JavaScript\JTokenizer;

class JsCompiler
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface[]
     */
    private $compilerPasses;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface[] $compilerPasses
     */
    public function __construct(array $compilerPasses)
    {
        $this->compilerPasses = $compilerPasses;
    }

    public function compile(string $content): string
    {
        $node = JParser::parse_string($content, true, JParser::class, JTokenizer::class);
        /* @var $node \PLUG\JavaScript\JNodes\nonterminal\JProgramNode */

        foreach ($this->compilerPasses as $compilerPass) {
            $compilerPass->process($node);
        }

        return $node->format();
    }
}
