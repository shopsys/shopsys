<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Parser;

use PLUG\JavaScript\JLexBase; // JLexBase contains J_* constants
use PLUG\JavaScript\JNodes\JNodeBase;

class JsStringParser
{
    public function getConcatenatedString(JNodeBase $node): string
    {
        if ($node->scalar_symbol() === J_STRING_LITERAL) {
            return $this->parseStringLiteral((string)$node);
        }

        if ($node->scalar_symbol() === J_ADD_EXPR) {
            $concatenatedString = '';

            $addExprNode = $node->reset();
            do {
                if ($addExprNode->scalar_symbol() === J_STRING_LITERAL) {
                    $concatenatedString .= $this->parseStringLiteral((string)$addExprNode);
                } elseif ($addExprNode->scalar_symbol() === J_NUMERIC_LITERAL) {
                    $concatenatedString .= (string)$addExprNode;
                } elseif ($addExprNode->scalar_symbol() === '+') {
                    continue;
                } else {
                    throw new \Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\UnsupportedNodeException();
                }
            } while ($addExprNode = $node->next());

            return $concatenatedString;
        }

        throw new \Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\UnsupportedNodeException();
    }
    
    private function parseStringLiteral(string $stringLiteral): string
    {
        return json_decode($this->normalizeStringLiteral($stringLiteral));
    }
    
    private function normalizeStringLiteral(string $stringLiteral): string
    {
        $matches = [];
        if (preg_match('/^"(.*)"$/', $stringLiteral, $matches)) {
            $doubleQuotesEscaped = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $stringLiteral, $matches)) {
            $singleQuotesEscaped = $matches[1];
            $unescaped = preg_replace("/\\\\'/", "'", $singleQuotesEscaped);
            $doubleQuotesEscaped = preg_replace('/"/', '\\"', $unescaped);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Javascript\Parser\Exception\UnsupportedNodeException();
        }

        return '"' . $doubleQuotesEscaped . '"';
    }
}
