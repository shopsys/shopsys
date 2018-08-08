<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class CollateFunction extends FunctionNode
{
    /**
     * @var \Doctrine\ORM\Query\AST\Node
     */
    private $inputStringExpression;

    /**
     * @var string
     */
    private $collation;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->inputStringExpression = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_STRING);
        $this->collation = $parser->getLexer()->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '%s COLLATE %s',
            $this->inputStringExpression->dispatch($sqlWalker),
            $sqlWalker->getConnection()->quoteIdentifier($this->collation)
        );
    }
}
