<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use InvalidArgumentException;
use Override;

class CollateExpressionNode extends FunctionNode
{
    protected readonly PathExpression|string $expression;

    protected readonly string $collation;

    /**
     * @param \Doctrine\ORM\Query\AST\PathExpression $expression
     * @param string $collation
     */
    public function __construct($expression = null, $collation = null)
    {
        parent::__construct('COLLATE');

        if (!$expression instanceof PathExpression && !is_string($expression)) {
            throw new InvalidArgumentException('Expression must be an instance of PathExpression or string');
        }

        if (!is_string($collation)) {
            throw new InvalidArgumentException('Collation must be a string');
        }

        $this->expression = $expression;
        $this->collation = $collation;
    }

    #[Override]
    public function parse(Parser $parser): void
    {
        // This is not used as we create the node manually
    }

    #[Override]
    public function getSql(SqlWalker $sqlWalker): string
    {
        if (is_string($this->expression)) {
            $queryComponents = $sqlWalker->getQueryComponents();

            if (isset($queryComponents[$this->expression]['resultVariable'])) {
                $resultVariable = $queryComponents[$this->expression]['resultVariable'];
                $sql = $resultVariable->dispatch($sqlWalker);
            } else {
                $sql = $this->expression;
            }
        } else {
            $sql = $this->expression->dispatch($sqlWalker);
        }

        return $sql . ' COLLATE "' . $this->collation . '"';
    }
}
