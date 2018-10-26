<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use Nette\Utils\Strings;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MissingReturnAnnotationFixer extends AbstractMissingAnnotationsFixer
{
    /**
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Methods and functions has to have @return annotation',
            [new CodeSample(
<<<'SAMPLE'
function someFunction(): int
{
}
SAMPLE
            ), new CodeSample(
<<<'SAMPLE'
/**
 * @return int
 */
function someFunction(): int
{
}
SAMPLE
            )]
        );
    }

    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @param \PhpCsFixer\Tokenizer\Token|null $docToken
     */
    protected function processFunctionToken(Tokens $tokens, int $index, ?Token $docToken): void
    {
        $returnTypeAnalysis = $this->functionsAnalyzer->getFunctionReturnType($tokens, $index);
        $type = $this->phpToDocTypeTransformer->transform($tokens, $returnTypeAnalysis);

        if ($this->shouldSkip($type, $docToken)) {
            return;
        }

        $indent = $this->resolveIndent($tokens, $index);
        $newLine = new Line(sprintf(
            '%s * @return %s%s',
            $indent,
            $type,
            $this->whitespacesFixerConfig->getLineEnding()
        ));

        if ($docToken) {
            $this->updateDocWithLines($tokens, $index, $docToken, [$newLine]);
            return;
        }

        $this->addDocWithLines($tokens, $index, [$newLine], $indent);
    }

    /**
     * @param string $type
     * @param \PhpCsFixer\Tokenizer\Token|null $docToken
     * @return bool
     */
    private function shouldSkip(string $type, ?Token $docToken): bool
    {
        if (!$type || $type === 'void') {
            return true;
        }

        return $docToken && Strings::contains($docToken->getContent(), '@return');
    }
}
