<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use Nette\Utils\Strings;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Shopsys\CodingStandards\Helper\PhpdocRegex;

final class MissingParamAnnotationsFixer extends AbstractMissingAnnotationsFixer
{
    /**
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Methods and functions has to have @param annotation for all params and @return annotation',
            [new CodeSample(
                <<<'SAMPLE'
function someFunction(int $value)
{
}
SAMPLE
            ), new CodeSample(
                <<<'SAMPLE'
/**
 * @param int
 */
function someFunction(int $value)
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
        $argumentAnalyses = $this->functionsAnalyzer->getFunctionArguments($tokens, $index);
        if (!count($argumentAnalyses)) {
            return;
        }

        if ($docToken) {
            $doc = new DocBlock($docToken->getContent());
            $lastParamLine = null;

            foreach ($doc->getAnnotationsOfType('param') as $annotation) {
                $matches = Strings::match($annotation->getContent(), PhpdocRegex::ARGUMENT_NAME_PATTERN);
                if (isset($matches[1])) {
                    unset($argumentAnalyses[$matches[1]]);
                }

                $lastParamLine = max($lastParamLine, $annotation->getEnd());
            }
        }

        // all arguments have annotations → skip
        if (!count($argumentAnalyses)) {
            return;
        }

        $indent = $this->resolveIndent($tokens, $index);

        $newLines = [];
        foreach ($argumentAnalyses as $argumentAnalysis) {
            $type = $this->phpToDocTypeTransformer->transform($tokens, $argumentAnalysis->getTypeAnalysis(), $argumentAnalysis->getDefault());

            $newLines[] = new Line(sprintf(
                '%s * @param %s %s%s',
                $indent,
                $type,
                $argumentAnalysis->getName(),
                $this->whitespacesFixerConfig->getLineEnding()
            ));
        }

        if ($docToken) {
            $this->updateDocWithLines($tokens, $index, $docToken, $newLines, $lastParamLine);
            return;
        }

        $this->addDocWithLines($tokens, $index, $newLines, $indent);
    }
}
