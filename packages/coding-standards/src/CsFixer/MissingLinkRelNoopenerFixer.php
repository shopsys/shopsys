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

final class MissingLinkRelNoopenerFixer implements FixerInterface
{
    use AppendsHtmlAttributeTrait;

    private const string NOOPENER = 'noopener';

    private const string LINK_OPENING_TAG_PATTERN = '@(<a\b)((?:"[^"]*"|\'[^\']*\'|[^>"\'])*?)(\s*/?>)@imsu';

    private const string BLANK_TARGET_ATTRIBUTE_PATTERN
        = '@(?:^|\s)target\s*=\s*(?:"_blank"|\'_blank\'|_blank(?=[\s/]|$))@isu';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Adds mandatory rel="noopener" to <a> HTML tag with a target="_blank" attribute.',
            [
                new CodeSample('<a href="https://example.com" target="_blank">label</a>'),
                new CodeSample('<a href="https://example.com" target="_blank" rel="nofollow">label</a>'),
                new CodeSample("<a\n    href=\"https://example.com\"\n    target=\"_blank\"\n>label</a>"),
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
        $originalCode = $tokens->generateCode();
        $code = preg_replace_callback(
            self::LINK_OPENING_TAG_PATTERN,
            function ($matches) {
                $beginning = $matches[1];
                $attributes = $matches[2];
                $end = $matches[3];

                if (!preg_match(self::BLANK_TARGET_ATTRIBUTE_PATTERN, $attributes)) {
                    return $beginning . $attributes . $end;
                }

                return $beginning . $this->addNoopenerToRel($attributes) . $end;
            },
            $originalCode,
        );

        // the pattern is UTF-8 aware, so a template with broken encoding makes the match fail — leave it alone
        $tokens->setCode($code ?? $originalCode);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'Shopsys/missing_link_rel_noopener';
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

    /**
     * @param string $attributes everything between the tag name and the closing bracket of a single <a> tag
     */
    private function addNoopenerToRel(string $attributes): string
    {
        if (!preg_match('@(?:^|\s)rel\s*=@sui', $attributes)) {
            return $this->appendHtmlAttribute($attributes, 'rel="' . self::NOOPENER . '"');
        }

        return preg_replace_callback(
            '@((?:^|\s)rel\s*=\s*(["\']))(.*?)\2@sui',
            static function ($matches) {
                $relValues = preg_split('@\s+@', trim($matches[3]), -1, PREG_SPLIT_NO_EMPTY);

                foreach ($relValues as $relValue) {
                    if (strcasecmp($relValue, self::NOOPENER) === 0) {
                        return $matches[0];
                    }
                }

                $relValues[] = self::NOOPENER;

                return $matches[1] . implode(' ', $relValues) . $matches[2];
            },
            $attributes,
            1,
        );
    }
}
