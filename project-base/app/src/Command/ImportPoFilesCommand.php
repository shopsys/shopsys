<?php

declare(strict_types=1);

namespace App\Command;

use JMS\TranslationBundle\Translation\FileWriter;
use JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\PoDumper;
use Shopsys\FrameworkBundle\Component\Translation\PoFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImportPoFilesCommand extends Command
{
    public const SOURCE_TRANSLATION_DIR = 'sourceTranslationDir';
    public const TARGET_TRANSLATION_DIR = 'targetTranslationDir';
    public const INPUT_FILES_TO_PROCESS = 'messages';
    public const TYPE = 'po';

    private SymfonyLoaderAdapter $fileLoader;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'translation:import';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\PoFileLoader $fileLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Translation\PoDumper $poDumper
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(
        PoFileLoader $fileLoader,
        private Domain $domain,
        private PoDumper $poDumper,
        private Filesystem $filesystem,
    ) {
        $this->fileLoader = new SymfonyLoaderAdapter($fileLoader);

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Replace translated messages into translated text in the target language.')
            ->addArgument(self::SOURCE_TRANSLATION_DIR, InputArgument::REQUIRED, 'Source directory with translations to process')
            ->addArgument(self::TARGET_TRANSLATION_DIR, InputArgument::REQUIRED, 'Target directory with production translations')
            ->addArgument(self::INPUT_FILES_TO_PROCESS, InputArgument::IS_ARRAY, 'Files to upload and process (messages, validators, etc...)');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domains = $this->domain->getAll();
        $targetDir = $input->getArgument(self::TARGET_TRANSLATION_DIR);
        $fileWriter = new FileWriter([self::TYPE => $this->poDumper]);
        foreach ($domains as $domain) {
            $locale = $domain->getLocale();
            foreach ($input->getArgument(self::INPUT_FILES_TO_PROCESS) as $inputFile) {
                $targetFileName = $targetDir . $inputFile . '.' . $locale . '.' . self::TYPE;
                $sourceTranslationFileName = $input->getArgument(self::SOURCE_TRANSLATION_DIR) . $inputFile . '.' . $locale . '.' . self::TYPE;

                if (!file_exists($sourceTranslationFileName) || !file_exists($targetFileName)) {
                    continue;
                }

                $sourceFileResource = new FileResource($sourceTranslationFileName);
                $targetFileResource = new FileResource($targetFileName);
                $sourceTranslationCatalog = $this->fileLoader->load($sourceFileResource, $locale);
                $translationCatalog = $this->fileLoader->load($targetFileResource, $locale);

                // If all messages (msgstr) are empty in the source translations, we will skip
                if (count($sourceTranslationCatalog->getDomains()) !== 0) {
                    $sourceTranslationCatalog->merge($translationCatalog);
                    $fileWriter->write($sourceTranslationCatalog, 'messages', $targetFileResource->getResource(), self::TYPE);
                    $output->writeln('<fg=green> Translations messages in : ' . $targetFileName . ' was updated </fg=green>');
                }
                $this->filesystem->remove($sourceTranslationFileName);
            }
        }

        return Command::SUCCESS;
    }
}
