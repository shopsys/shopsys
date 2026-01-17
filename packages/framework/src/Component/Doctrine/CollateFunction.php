<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Override;

class CollateFunction extends FunctionNode
{
    protected Node $inputStringExpression;

    protected string $collation;

    #[Override]
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

    /**
     * @return string
     */
    #[Override]
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            '%s COLLATE %s',
            $this->inputStringExpression->dispatch($sqlWalker),
            $sqlWalker->getConnection()->quoteIdentifier($this->collation),
        );
    }
}
