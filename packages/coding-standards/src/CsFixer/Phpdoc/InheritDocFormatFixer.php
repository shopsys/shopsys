<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class InheritDocFormatFixer implements FixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            '{@inheritdoc} annotations have to be in the specific format',
            [new CodeSample(
                <<<'SAMPLE'
/**
 * {@inheritdoc}
 */
public function transform($value): ?array
SAMPLE,
            )],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        /** @var \PhpCsFixer\Tokenizer\Token $token */
        foreach ($tokens->findGivenKind(T_DOC_COMMENT) as $index => $token) {
            $doc = new DocBlock($token->getContent());
            foreach ($doc->getLines() as $line) {
                if ($this->isInheritDocCandidate($line)) {
                    $this->fixInheritDoc($line);

                    $tokens[$index] = new Token([T_DOC_COMMENT, $doc->getContent()]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Shopsys/inherit_doc_format';
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(SplFileInfo $file): bool
    {
        return preg_match('/\.php$/ui', $file->getFilename()) === 1;
    }

    /**
     * @param \PhpCsFixer\DocBlock\Line $line
     * @return bool
     */
    private function isInheritDocCandidate(Line $line): bool
    {
        return preg_match('~\{?@[Ii]nherit[dD]oc}?~', $line->getContent()) === 1;
    }

    /**
     * @param \PhpCsFixer\DocBlock\Line $line
     */
    private function fixInheritDoc(Line $line): void
    {
        $line->setContent(
            preg_replace(
                '~\{?@[Ii]nherit[dD]oc}?~',
                '{@inheritdoc}',
                $line->getContent(),
            ),
        );
    }
}
