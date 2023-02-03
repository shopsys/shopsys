<?php

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Translation\JsFileExtractor;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use SplFileInfo;

class JsFileExtractorTest extends TestCase
{
    public function testExtract()
    {
        /*
         * the method that generates this file is tested elsewhere (project-base/assets/js/commands/translations/parseFile.test.js)
         */
        $fileName = 'translationsDump.json';

        $catalogue = $this->extract(__DIR__ . '/Resources/' . $fileName);
        $expected = new MessageCatalogue();

        $message = new Message('trans test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 3));
        $expected->add($message);

        $message = new Message('transChoice test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 5));
        $expected->add($message);

        $message = new Message('trans test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 7));
        $expected->add($message);

        $message = new Message('transChoice test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 9));
        $expected->add($message);

        $message = new Message('concatenated message', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 11));
        $expected->add($message);

        $this->assertEquals($expected, $catalogue);
    }

    /**
     * @param mixed $filename
     */
    private function extract($filename)
    {
        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('The file "%s" does not exist.', $filename));
        }
        $file = new SplFileInfo($filename);

        $extractor = $this->getExtractor();

        $catalogue = new MessageCatalogue();
        $extractor->visitFile($file, $catalogue);

        return $catalogue;
    }

    private function getExtractor()
    {
        return new JsFileExtractor();
    }
}
