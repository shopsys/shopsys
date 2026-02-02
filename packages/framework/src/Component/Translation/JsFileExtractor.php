<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Override;
use SplFileInfo;
use Twig\Node\Node;

class JsFileExtractor implements FileVisitorInterface
{
    protected const DUMP_FILE = 'translationsDump.json';

    #[Override]
    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue): void
    {
        if ($this->isFileTranslationDump($file) === false) {
            return;
        }

        $translationDumpContent = file_get_contents($file->getPathname());

        if ($translationDumpContent === false) {
            return;
        }

        $translationsDump = json_decode($translationDumpContent, true);

        foreach ($translationsDump as $translation) {
            $message = new Message(
                $translation['id'],
                $translation['domain'] ?? Translator::DEFAULT_TRANSLATION_DOMAIN,
            );
            $message->addSource(new FileSource(
                $translation['source'],
                $translation['line'],
            ));

            $catalogue->add($message);
        }
    }

    protected function isFileTranslationDump(SplFileInfo $file): bool
    {
        return $file->getFilename() === static::DUMP_FILE;
    }

    #[Override]
    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast): void
    {
    }

    #[Override]
    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, Node $ast): void
    {
    }
}
