<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class ForbiddenDicAliasShortSyntaxFixer implements FixerInterface, DefinedFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Forces longer syntax of DIC service aliases',
            [
                new CodeSample('last work.___'),
            ]
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
     * @param string $code
     * @return string
     */
    private function fixShortAliasSyntax(string $code): string
    {
        return preg_replace(
            '~^( +)([\w\\\\]+): \'@([\w\\\\]+)\'$~m',
            "$1$2:\n$1$1alias: $3",
            $code
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $code = $this->fixShortAliasSyntax($tokens->generateCode());

        $tokens->setCode($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Shopsys/forbidden_dic_alias_short_syntax';
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
        return preg_match('/\.ya?ml$/ui', $file->getFilename()) === 1;
    }
}
