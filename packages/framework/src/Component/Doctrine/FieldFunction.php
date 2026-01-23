<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;
use Override;

class FieldFunction extends FunctionNode
{
    protected Node $firstArgumentExpression;

    /**
     * @var \Doctrine\ORM\Query\AST\Node[]
     */
    protected array $nextArgumentExpressions;

    #[Override]
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->firstArgumentExpression = $parser->ArithmeticExpression();

        $lexer = $parser->getLexer();
        $this->nextArgumentExpressions = [];

        while ($lexer->lookahead->type === TokenType::T_COMMA) {
            $parser->match(TokenType::T_COMMA);
            $this->nextArgumentExpressions[] = $parser->ArithmeticPrimary();
        }
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    #[Override]
    public function getSql(SqlWalker $sqlWalker): string
    {
        $values = array_map(function (Node $argumentExpression) use ($sqlWalker) {
            return $argumentExpression->dispatch($sqlWalker);
        }, $this->nextArgumentExpressions);

        return 'FIELD(' . $this->firstArgumentExpression->dispatch($sqlWalker) . ',ARRAY[' . implode(
            ',',
            $values,
        ) . '])';
    }
}
