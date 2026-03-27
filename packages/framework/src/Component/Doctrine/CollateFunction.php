<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;
use Override;

class CollateFunction extends FunctionNode
{
    protected Node $inputStringExpression;

    protected string $collation;

    #[Override]
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->inputStringExpression = $parser->StringExpression();
        $parser->match(TokenType::T_COMMA);
        $parser->match(TokenType::T_STRING);
        $this->collation = $parser->getLexer()->token->value;
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    #[Override]
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            '%s COLLATE %s',
            $this->inputStringExpression->dispatch($sqlWalker),
            $sqlWalker->getConnection()->quoteSingleIdentifier($this->collation),
        );
    }
}
