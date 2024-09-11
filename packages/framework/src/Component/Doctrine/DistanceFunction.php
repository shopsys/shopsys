<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class DistanceFunction extends FunctionNode
{
    protected Node $latitudeFrom;

    protected Node $longitudeFrom;

    protected Node $latitudeTo;

    protected Node $longitudeTo;

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->latitudeFrom = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->longitudeFrom = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->latitudeTo = $parser->SimpleArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->longitudeTo = $parser->SimpleArithmeticExpression();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        // Earth radius is a constant, so it doesn't need dispatch
        $earthRadius = 6371000;

        return "
            ( {$earthRadius} * ATAN2(
                SQRT(
                    POW(COS(RADIANS(CAST({$this->latitudeTo->dispatch($sqlWalker)} AS NUMERIC))) * SIN(RADIANS(CAST({$this->longitudeTo->dispatch($sqlWalker)} AS NUMERIC)) - RADIANS(CAST({$this->longitudeFrom->dispatch($sqlWalker)} AS NUMERIC))), 2) +
                    POW(COS(RADIANS(CAST({$this->latitudeFrom->dispatch($sqlWalker)} AS NUMERIC))) * SIN(RADIANS(CAST({$this->latitudeTo->dispatch($sqlWalker)} AS NUMERIC))) - SIN(RADIANS(CAST({$this->latitudeFrom->dispatch($sqlWalker)} AS NUMERIC))) * COS(RADIANS(CAST({$this->latitudeTo->dispatch($sqlWalker)} AS NUMERIC))) * COS(RADIANS(CAST({$this->longitudeTo->dispatch($sqlWalker)} AS NUMERIC)) - RADIANS(CAST({$this->longitudeFrom->dispatch($sqlWalker)} AS NUMERIC))), 2)
                ),
                SIN(RADIANS(CAST({$this->latitudeFrom->dispatch($sqlWalker)} AS NUMERIC))) * SIN(RADIANS(CAST({$this->latitudeTo->dispatch($sqlWalker)} AS NUMERIC))) + COS(RADIANS(CAST({$this->latitudeFrom->dispatch($sqlWalker)} AS NUMERIC))) * COS(RADIANS(CAST({$this->latitudeTo->dispatch($sqlWalker)} AS NUMERIC))) * COS(RADIANS(CAST({$this->longitudeTo->dispatch($sqlWalker)} AS NUMERIC)) - RADIANS(CAST({$this->longitudeFrom->dispatch($sqlWalker)} AS NUMERIC)))
            ))
        ";
    }
}
