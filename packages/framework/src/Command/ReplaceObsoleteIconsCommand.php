<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'upgrade:replace-obsolete-icons', description: 'Replaces obsolete icons rendered with <i> tag by {{ ux_icons() }} function calls in Twig templates')]
final class ReplaceObsoleteIconsCommand extends Command
{
    public function __construct(
        private readonly string $kernelProjectDir,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'The file or directory path containing Twig templates',
            $this->kernelProjectDir . '/templates',
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');

        $symfonyStyle = new SymfonyStyle($input, $output);

        if (!file_exists($path)) {
            $symfonyStyle->error(sprintf('The path "%s" does not exist.', $path));

            return Command::FAILURE;
        }

        $finder = new Finder();

        if (is_dir($path)) {
            $finder->files()->in($path)->name('*.twig');
        } else {
            $finder->append([$path]);
        }

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();

            $content = file_get_contents($filePath);

            $newContent = $this->replaceIconsAsItalicTag($content);
            $newContent = $this->replaceIconsInOtherTags($newContent);

            if ($newContent === $content) {
                continue;
            }

            file_put_contents($filePath, $newContent);

            $symfonyStyle->listing([sprintf('Updated file: %s', $filePath)]);
        }

        $symfonyStyle->success('Processing completed.');

        return Command::SUCCESS;
    }

    private function replaceIconsAsItalicTag(string $content): string
    {
        return preg_replace_callback(
            '~<i\b[^>]*class="[^"]*\bsvg-[\w-]+\b[^"]*"[^>]*></i>~i',
            function ($matches) {
                $iconTag = $matches[0];

                preg_match(
                    '/<i\b([^>]*)class="([^"]*\bsvg-([\w-]+)\b[^"]*)"([^>]*)><\/i>/i',
                    $iconTag,
                    $iconMatches,
                );

                if (!$iconMatches) {
                    return $iconTag;
                }

                $attributes = $iconMatches[1] . ' ' . $iconMatches[4];
                $allClasses = $iconMatches[2];
                $iconName = $iconMatches[3]; // Extracted icon name

                $additionalClasses = $this->getNormalizedAdditionalClasses($allClasses, $iconName);
                $additionalAttributes = $this->getNormalizedAttributes($attributes);

                $uxIconFunction = sprintf('{{ ux_icon(\'%s\'', $iconName);

                if ($additionalClasses && $additionalAttributes) {
                    $uxIconFunction .= sprintf(', { class: \'%s\'%s })', $additionalClasses, $additionalAttributes);
                } elseif ($additionalClasses) {
                    $uxIconFunction .= sprintf(', { class: \'%s\' })', $additionalClasses);
                } elseif ($additionalAttributes) {
                    $uxIconFunction .= sprintf(', { %s })', $additionalAttributes);
                } else {
                    $uxIconFunction .= ')';
                }

                return $uxIconFunction . ' }}';
            },
            $content,
        );
    }

    private function replaceIconsInOtherTags(string $content): string
    {
        $tagList = ['a', 'span'];

        foreach ($tagList as $tag) {
            $content = preg_replace_callback(
                '~<' . $tag . '\b[^>]*class="([^"]*\bsvg-([\w-]+)\b[^"]*)"[^>]*></' . $tag . '>~i',
                function ($matches) use ($tag) {
                    $tagContent = $matches[0];
                    $classes = $matches[1];
                    $iconName = $matches[2];

                    return str_replace(
                        [
                            $classes,
                            '></' . $tag . '>',
                        ],
                        [
                            $this->getNormalizedAdditionalClasses($classes, $iconName),
                            sprintf('>{{ ux_icon(\'%s\') }}</' . $tag . '>', $iconName),
                        ],
                        $tagContent,
                    );
                },
                $content,
            );
        }

        return $content;
    }

    private function getNormalizedAdditionalClasses(string $allClasses, string $iconName): string
    {
        $additionalClasses = array_map(
            static fn ($class) => (trim($class) !== '') ? trim($class) : null,
            explode(' ', $allClasses),
        );

        $additionalClasses = array_filter($additionalClasses, static fn ($class) => $class !== 'svg-' . $iconName && $class !== 'svg' && $class !== null);

        return implode(' ', $additionalClasses);
    }

    private function getNormalizedAttributes(string $inputAttributes): string
    {
        $inputAttributes = trim($inputAttributes);

        $attributes = [];

        if ($inputAttributes !== '') {
            preg_match_all('/([\w-]+)="([^"]*)"/', $inputAttributes, $attrMatches, PREG_SET_ORDER);

            foreach ($attrMatches as $attrMatch) {
                $attributes[$attrMatch[1]] = $attrMatch[2];
            }
        }

        $twigFormattedAttributes = [];

        foreach ($attributes as $key => $value) {
            $twigFormattedAttributes[] = sprintf('\'%s\': %s', $key, $this->convertTwigString($value));
        }

        return implode(', ', $twigFormattedAttributes);
    }

    private function convertTwigString(string $value): string
    {
        $replaced = str_replace(['{{', '}}'], '', $value);

        $replaced = trim($replaced);

        if (!str_starts_with($replaced, '\'')) {
            return sprintf('\'%s\'', $replaced);
        }

        return $replaced;
    }
}
