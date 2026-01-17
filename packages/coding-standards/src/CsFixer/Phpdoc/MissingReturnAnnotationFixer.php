<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use Nette\Utils\Strings;
use Override;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class MissingReturnAnnotationFixer extends AbstractMissingAnnotationsFixer
{
    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Methods and functions have to have @return annotation',
            [new CodeSample('function someFunction(): int {}')],
        );
    }

    #[Override]
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
            $this->whitespacesFixerConfig->getLineEnding(),
        ));

        if ($docToken !== null) {
            $this->updateDocWithLines($tokens, $index, $docToken, [$newLine]);

            return;
        }

        $this->addDocWithLines($tokens, $index, [$newLine], $indent);
    }

    private function shouldSkip(string $type, ?Token $docToken): bool
    {
        if (in_array($type, ['', 'void', 'mixed', 'never'], true)) {
            return true;
        }

        return $docToken && Strings::contains($docToken->getContent(), '@return');
    }
}
