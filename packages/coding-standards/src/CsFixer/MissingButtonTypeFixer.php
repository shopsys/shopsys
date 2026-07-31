<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class MissingButtonTypeFixer implements FixerInterface
{
    use AppendsHtmlAttributeTrait;

    private const string BUTTON_OPENING_TAG_PATTERN = '@(<button\b)((?:"[^"]*"|\'[^\']*\'|[^>"\'])*?)(\s*/?>)@imsu';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Adds mandatory type attribute to <button> HTML tag.',
            [
                new CodeSample('<button/>'),
                new CodeSample('<button>label</button>'),
                new CodeSample("<button\n    class=\"btn\"\n/>"),
            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isRisky(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $code = preg_replace_callback(
            static::BUTTON_OPENING_TAG_PATTERN,
            function ($matches) {
                $beginning = $matches[1];
                $attributes = $matches[2];
                $end = $matches[3];

                if (!preg_match('@(?:^|\s)type\s*=@i', $attributes)) {
                    $attributes = $this->appendHtmlAttribute($attributes, 'type="button"');
                }

                return $beginning . $attributes . $end;
            },
            $tokens->generateCode(),
        );

        $tokens->setCode($code);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'Shopsys/missing_button_type';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supports(SplFileInfo $file): bool
    {
        return preg_match('/\.html(?:\.twig)?$/ui', $file->getFilename()) === 1;
    }
}
