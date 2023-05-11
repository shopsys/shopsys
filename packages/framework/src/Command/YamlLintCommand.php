<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Traversable;
use function count;
use function in_array;

class YamlLintCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'lint:yaml';

    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    private $parser;

    /**
     * @var string
     */
    private $format;

    /**
     * @var bool
     */
    private $displayCorrectFiles;

    public function __construct()
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Lints a file and outputs encountered errors')
            ->addArgument('filename', null, 'A file or a directory or STDIN')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The output format', 'txt')
            ->addOption('parse-tags', null, InputOption::VALUE_NONE, 'Parse custom tags')
            ->addOption('exclude-regex', null, InputOption::VALUE_REQUIRED, 'Regex for exclude path from check')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command lints a YAML file and outputs to STDOUT
the first encountered syntax error.

You can validates YAML contents passed from STDIN:

  <info>cat filename | php %command.full_name%</info>

You can also validate the syntax of a file:

  <info>php %command.full_name% filename</info>

Or of a whole directory:

  <info>php %command.full_name% dirname</info>
  <info>php %command.full_name% dirname --format=json</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');
        $this->format = $input->getOption('format');
        $this->displayCorrectFiles = $output->isVerbose();
        $flags = $input->getOption('parse-tags') ? Yaml::PARSE_CUSTOM_TAGS : 0;
        $excludeRegex = $input->getOption('exclude-regex');

        if (!$filename) {
            $stdin = $this->getStdin();

            if (!$stdin) {
                throw new RuntimeException('Please provide a filename or pipe file content to STDIN.');
            }

            return $this->display($io, [$this->validate($stdin, $flags)]);
        }

        if (!$this->isReadable($filename)) {
            throw new RuntimeException(sprintf('File or directory "%s" is not readable.', $filename));
        }

        $filesInfo = [];
        /** @var \SplFileInfo $file */
        foreach ($this->getFiles($filename, $excludeRegex) as $file) {
            $filesInfo[] = $this->validate(file_get_contents($file->getPathname()), $flags, $file);
        }

        return $this->display($io, $filesInfo);
    }

    /**
     * @param string $content
     * @param int $flags
     * @param \SplFileInfo|null $file
     * @return array
     */
    protected function validate(string $content, int $flags, ?SplFileInfo $file = null): array
    {
        $prevErrorHandler = set_error_handler(function ($level, $message, $file, $line) use (&$prevErrorHandler) {
            if ($level === E_USER_DEPRECATED) {
                throw new ParseException($message, $this->getParser()->getRealCurrentLineNb() + 1);
            }

            return $prevErrorHandler ? $prevErrorHandler($level, $message, $file, $line) : false;
        });

        try {
            $this->getParser()->parse($content, Yaml::PARSE_CONSTANT | $flags);
        } catch (ParseException $e) {
            return ['file' => $file, 'line' => $e->getParsedLine(), 'valid' => false, 'message' => $e->getMessage()];
        } finally {
            restore_error_handler();
        }

        return ['file' => $file, 'valid' => true];
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param array $files
     * @return int
     */
    protected function display(SymfonyStyle $io, array $files): int
    {
        switch ($this->format) {
            case 'txt':
                return $this->displayTxt($io, $files);
            case 'json':
                return $this->displayJson($io, $files);
            default:
                throw new InvalidArgumentException(sprintf('The format "%s" is not supported.', $this->format));
        }
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param array $filesInfo
     * @return int
     */
    protected function displayTxt(SymfonyStyle $io, array $filesInfo): int
    {
        $countFiles = count($filesInfo);
        $erroredFiles = 0;

        foreach ($filesInfo as $info) {
            if ($info['valid'] && $this->displayCorrectFiles) {
                $io->comment('<info>OK</info>' . ($info['file'] ? sprintf(' in %s', $info['file']) : ''));
            } elseif (!$info['valid']) {
                ++$erroredFiles;
                $io->text('<error> ERROR </error>' . ($info['file'] ? sprintf(' in %s', $info['file']) : ''));
                $io->text(sprintf('<error> >> %s</error>', $info['message']));
            }
        }

        if ($erroredFiles === 0) {
            $io->success(sprintf('All %d YAML files contain valid syntax.', $countFiles));
        } else {
            $io->warning(
                sprintf(
                    '%d YAML files have valid syntax and %d contain errors.',
                    $countFiles - $erroredFiles,
                    $erroredFiles
                )
            );
        }

        return min($erroredFiles, 1);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param array $filesInfo
     * @return int
     */
    protected function displayJson(SymfonyStyle $io, array $filesInfo): int
    {
        $errors = 0;

        array_walk($filesInfo, function (&$v) use (&$errors) {
            $v['file'] = (string)$v['file'];

            if (!$v['valid']) {
                ++$errors;
            }
        });

        $io->writeln(json_encode($filesInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return min($errors, 1);
    }

    /**
     * @param string $fileOrDirectory
     * @param string|null $excludeRegex
     * @return \Traversable
     */
    protected function getFiles(string $fileOrDirectory, ?string $excludeRegex = null): Traversable
    {
        if (is_file($fileOrDirectory) && $this->isFileInExcludePath($fileOrDirectory, $excludeRegex) === false) {
            yield new SplFileInfo($fileOrDirectory);

            return;
        }

        foreach ($this->getDirectoryIterator($fileOrDirectory) as $file) {
            if (
                !in_array($file->getExtension(), ['yml', 'yaml'], true)
                || $this->isFileInExcludePath($file->getPathname(), $excludeRegex) === true
            ) {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @param string $file
     * @param string|null $excludeRegex
     * @return bool
     */
    protected function isFileInExcludePath(string $file, ?string $excludeRegex): bool
    {
        if ($excludeRegex === null) {
            return false;
        }

        return preg_match($excludeRegex, $file) === 1;
    }

    /**
     * @return string|null
     */
    protected function getStdin(): ?string
    {
        if (ftell(STDIN) !== 0) {
            return null;
        }

        $inputs = '';

        while (!feof(STDIN)) {
            $inputs .= fread(STDIN, 1024);
        }

        return $inputs;
    }

    /**
     * @return \Symfony\Component\Yaml\Parser
     */
    protected function getParser(): Parser
    {
        if ($this->parser === null) {
            $this->parser = new Parser();
        }

        return $this->parser;
    }

    /**
     * @param string $directory
     * @return \RecursiveIteratorIterator
     */
    protected function getDirectoryIterator(string $directory): RecursiveIteratorIterator
    {
        $default = function (string $directory) {
            return new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        };

        return $default($directory);
    }

    /**
     * @param string $fileOrDirectory
     * @return bool
     */
    protected function isReadable(string $fileOrDirectory): bool
    {
        $default = function ($fileOrDirectory) {
            return is_readable($fileOrDirectory);
        };

        return $default($fileOrDirectory);
    }
}
