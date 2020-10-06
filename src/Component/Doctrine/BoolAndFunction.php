<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class BoolAndFunction extends FunctionNode
{
    protected const FUNCTION_BOOL_AND = 'bool_and';

    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    public $stringExpression;

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringExpression = $parser->StringExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return static::FUNCTION_BOOL_AND . '(' . $this->stringExpression->dispatch($sqlWalker) . ')';
    }
}
