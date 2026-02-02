<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class I18nLocalesWorker extends AbstractWorker
{
    private const string I18N_FILE_PATH = 'storefront/i18n.js';
    private const string I18NEXT_PARSER_FILE_PATH = 'storefront/config/i18next-parser.config.js';

    /**
     * @param \Shopsys\Cli\Model\FileHandler $fileHandler
     */
    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates locales array in storefront/config/i18next-parser.config.js and storefront/i18n.js';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 464;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $i18nFilePath = $projectPath . '/' . self::I18N_FILE_PATH;
        $i18nContent = $this->fileHandler->readFile($i18nFilePath);

        $i18nextParserFile = $projectPath . '/' . self::I18NEXT_PARSER_FILE_PATH;
        $i18nextParserContent = $this->fileHandler->readFile($i18nextParserFile);

        $implodedLocales = implode("', '", $config->getUniqueLocales());

        $pattern = "/(\s*locales:\s*)\[[^]]*]/";

        $newI18nContent = preg_replace(
            $pattern,
            '${1}' . "['default', '" . $implodedLocales . "']",
            $i18nContent,
        );
        $this->fileHandler->writeFile(
            $i18nFilePath,
            $newI18nContent,
        );

        $newI18nextParserContent = preg_replace(
            $pattern,
            '${1}' . "['" . $implodedLocales . "']",
            $i18nextParserContent,
        );
        $this->fileHandler->writeFile(
            $i18nextParserFile,
            $newI18nextParserContent,
        );

        return WorkerResult::success(
            'Updated i18n locales configuration',
            filesModified: [self::I18N_FILE_PATH, self::I18NEXT_PARSER_FILE_PATH],
        );
    }
}
