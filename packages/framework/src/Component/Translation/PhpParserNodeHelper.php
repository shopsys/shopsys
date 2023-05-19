<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Shopsys\FrameworkBundle\Component\Translation\Exception\StringValueUnextractableException;
use SplFileInfo;

class PhpParserNodeHelper
{
    protected const TRANSLATOR_CLASS_FQN = '\Shopsys\FrameworkBundle\Component\Translation\Translator';

    /**
     * @param \PhpParser\Node $node
     * @param \SplFileInfo $fileInfo
     * @return string
     */
    public static function getConcatenatedStringValue(Node $node, SplFileInfo $fileInfo)
    {
        if ($node instanceof String_) {
            return $node->value;
        }

        if ($node instanceof Concat) {
            return self::getConcatenatedStringValue($node->left, $fileInfo) . self::getConcatenatedStringValue(
                $node->right,
                $fileInfo,
            );
        }

        if ($node instanceof ClassConstFetch && $node->class->parts[0] === 'Translator') {
            return constant(static::TRANSLATOR_CLASS_FQN . '::' . $node->name->name);
        }

        throw new StringValueUnextractableException(
            sprintf(
                'Can only extract the message ID or message domain from a scalar, concatenated string or "%s" class constant,'
                . ' but got "%s". Please refactor your code to make it extractable (in %s on line %d).',
                static::TRANSLATOR_CLASS_FQN,
                get_class($node),
                $fileInfo,
                $node->getLine(),
            ),
        );
    }
}
