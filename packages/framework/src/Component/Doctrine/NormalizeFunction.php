<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class NormalizeFunction extends FunctionNode
{
    const FUNCTION_NORMALIZE = 'normalize';

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    public $stringExpression;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringExpression = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return self::FUNCTION_NORMALIZE . '(' . $this->stringExpression->dispatch($sqlWalker) . ')';
    }
}
