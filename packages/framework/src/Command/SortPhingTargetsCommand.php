<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SortPhingTargetsCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;
    protected const ARG_XML_PATH = 'xml';
    protected const OPTION_ONLY_CHECK = 'check';

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:phing-targets:sort';

    protected function configure(): void
    {
        $this
            ->setDescription('Sort Phing targets alphabetically')
            ->addArgument(static::ARG_XML_PATH, InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Path(-s) to the Phing XML configuration')
            ->addOption(static::OPTION_ONLY_CHECK, null, InputOption::VALUE_NONE, 'Will not modify the XML, only fail if the output would be different');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $returnCode = static::RETURN_CODE_OK;
        $io = new SymfonyStyle($input, $output);

        $checkOnly = $input->getOption(static::OPTION_ONLY_CHECK);
        $paths = $input->getArgument(static::ARG_XML_PATH);
        foreach ($paths as $path) {
            $content = file_get_contents($path);

            $sortedContent = $this->sortTargetBlocks($content, $io);

            $isContentChanged = $content !== $sortedContent;
            if ($checkOnly && $isContentChanged) {
                $returnCode = static::RETURN_CODE_ERROR;

                $io->error(sprintf('The targets in "%s" are not alphabetically sorted.', $path));
            } elseif ($isContentChanged) {
                file_put_contents($path, $sortedContent);

                $io->success(sprintf('The targets in "%s" were alphabetically sorted.', $path));
            }
        }

        if ($returnCode === static::RETURN_CODE_OK) {
            $io->success('All targets are alphabetically sorted.');
        } elseif ($returnCode === static::RETURN_CODE_ERROR) {
            $io->error('Some targets are not alphabetically sorted.');

            $io->comment(sprintf('Re-run the command without the "%s" option to fix it automatically.', static::OPTION_ONLY_CHECK));
        }

        return $returnCode;
    }

    /**
     * @param string $content
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return string
     */
    protected function sortTargetBlocks(string $content, SymfonyStyle $io): string
    {
        $targetBlocks = $this->extractTargetBlocksIndexedByName($content);
        $content = $this->replaceTargetsByPlaceholders($content, $targetBlocks, $io);
        $content = $this->normalizeWhitespaceBetweenPlaceholders($content);

        ksort($targetBlocks);

        $content = $this->replacePlaceholdersByTargets($content, $targetBlocks);

        return $content;
    }

    /**
     * @param string $content
     * @return string[]
     */
    protected function extractTargetBlocksIndexedByName(string $content): array
    {
        $xml = new SimpleXMLElement($content);

        $targetBlocks = [];
        foreach ($xml as $tagName => $item) {
            if ($tagName === 'target') {
                $targetBlocks[(string)$item['name']] = $item->asXML();
            }
        }

        return $targetBlocks;
    }

    /**
     * @param int $position
     * @return string
     */
    protected function getTargetPlaceholder(int $position): string
    {
        return '<!--- TARGET ' . $position . ' -->';
    }

    /**
     * @param string $content
     * @return string
     */
    protected function normalizeWhitespaceBetweenPlaceholders(string $content): string
    {
        return preg_replace('~(<!--- TARGET \d+ -->)(?: *\n)*(?= *<!--- TARGET \d+ -->)~mu', "$1\n\n", $content);
    }

    /**
     * @param string $content
     * @param string[] $targetBlocks
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return string
     */
    protected function replaceTargetsByPlaceholders(string $content, array $targetBlocks, SymfonyStyle $io): string
    {
        $position = 1;
        foreach ($targetBlocks as $targetBlock) {
            $targetPlaceholder = $this->getTargetPlaceholder($position++);

            $replacedContent = str_replace($targetBlock, $targetPlaceholder, $content);

            if ($content === $replacedContent) {
                $io->warning("This target block was not found in the original XML:\n\n" . $targetBlock);

                $io->warning('This is probably because of unexpected formatting (eg. excessive whitespace). Position of this target will not be checked nor fixed.');
            }

            $content = $replacedContent;
        }

        return $content;
    }

    /**
     * @param string $content
     * @param string[] $targetBlocks
     * @return string
     */
    protected function replacePlaceholdersByTargets(string $content, array $targetBlocks): string
    {
        $position = 1;
        foreach ($targetBlocks as $targetBlock) {
            $targetPlaceholder = $this->getTargetPlaceholder($position++);

            $content = str_replace($targetPlaceholder, $targetBlock, $content);
        }
        return $content;
    }
}
