<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Translation\PhpFileExtractor;
use Shopsys\FrameworkBundle\Component\Translation\PhpFileExtractorFactory;
use Shopsys\FrameworkBundle\Component\Translation\PhpParserNodeHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use SplFileInfo;

class PhpFileExtractorTest extends TestCase
{
    public function testExtractController(): void
    {
        $fileName = 'Controller.php';

        $catalogue = $this->extract(__DIR__ . '/Resources/' . $fileName);

        $expected = new MessageCatalogue();

        $message = new Message('trans test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 19));
        $expected->add($message);

        $message = new Message('trans test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 20));
        $expected->add($message);

        $message = new Message('t test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 22));
        $expected->add($message);

        $message = new Message('t test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 23));
        $expected->add($message);

        $message = new Message('my %adjective% string', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 27));
        $expected->add($message);

        $message = new Message('my string with domain only', 'another-translation-domain');
        $message->addSource(new FileSource($fileName, 28));
        $expected->add($message);

        $message = new Message('my %adjective% string with domain', 'another-translation-domain');
        $message->addSource(new FileSource($fileName, 29));
        $expected->add($message);

        $message = new Message('my %adjective% string with named locale', 'someDomain');
        $message->addSource(new FileSource($fileName, 30));
        $expected->add($message);

        $message = new Message('my %adjective% string with unsorted arguments', 'unsortedDomain');
        $message->addSource(new FileSource($fileName, 31));
        $expected->add($message);

        $message = new Message('my %adjective% string with null domain', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 32));
        $expected->add($message);

        $this->assertEquals($expected, $catalogue);
    }

    private function getExtractor(): PhpFileExtractor
    {
        $phpFileExtractorFactory = new PhpFileExtractorFactory(new PhpParserNodeHelper());

        return $phpFileExtractorFactory->create();
    }

    private function extract(mixed $filename): MessageCatalogue
    {
        if (!is_file($filename)) {
            throw new RuntimeException(sprintf('The file "%s" does not exist.', $filename));
        }
        $file = new SplFileInfo($filename);

        $extractor = $this->getExtractor();

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createForVersion(PhpVersion::fromString('8.3'));
        $ast = $parser->parse(file_get_contents($file->getPathname()));

        $catalogue = new MessageCatalogue();
        $extractor->visitPhpFile($file, $catalogue, $ast);

        return $catalogue;
    }
}
