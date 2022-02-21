<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler;

use PLUG\JavaScript\JParser;
use PLUG\JavaScript\JTokenizer;

class JsCompiler
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface[]
     */
    protected $compilerPasses;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface[] $compilerPasses
     */
    public function __construct(array $compilerPasses)
    {
        $this->compilerPasses = $compilerPasses;
    }

    /**
     * @param string $content
     * @return string
     */
    public function compile($content)
    {
        /** @var \PLUG\JavaScript\JNodes\nonterminal\JProgramNode $node */
        $node = JParser::parse_string($content, true, JParser::class, JTokenizer::class);

        foreach ($this->compilerPasses as $compilerPass) {
            $compilerPass->process($node);
        }

        $format = $node->format();
        $node->free_memory();
        $node->destroy();
        unset($node);

        return $format;
    }
}
