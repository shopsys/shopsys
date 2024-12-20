<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Translation\PhpFileExtractorFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use SplFileInfo;

class PhpFileExtractorTest extends TestCase
{
    public function testExtractController()
    {
        $fileName = 'Controller.php';

        $catalogue = $this->extract(__DIR__ . '/Resources/' . $fileName);

        $expected = new MessageCatalogue();

        $message = new Message('trans test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 23));
        $expected->add($message);

        $message = new Message('trans test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 24));
        $expected->add($message);

        $message = new Message('t test', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 26));
        $expected->add($message);

        $message = new Message('t test with domain', 'testDomain');
        $message->addSource(new FileSource($fileName, 27));
        $expected->add($message);

        $message = new Message('my %adjective% string', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 31));
        $expected->add($message);

        $message = new Message('my string with domain only', 'another-translation-domain');
        $message->addSource(new FileSource($fileName, 32));
        $expected->add($message);

        $message = new Message('my %adjective% string with domain', 'another-translation-domain');
        $message->addSource(new FileSource($fileName, 33));
        $expected->add($message);

        $message = new Message('my %adjective% string with named locale', 'someDomain');
        $message->addSource(new FileSource($fileName, 34));
        $expected->add($message);

        $message = new Message('my %adjective% string with unsorted arguments', 'unsortedDomain');
        $message->addSource(new FileSource($fileName, 35));
        $expected->add($message);

        $message = new Message('my %adjective% string with null domain', Translator::DEFAULT_TRANSLATION_DOMAIN);
        $message->addSource(new FileSource($fileName, 36));
        $expected->add($message);

        $this->assertEquals($expected, $catalogue);
    }

    private function getExtractor()
    {
        $phpFileExtractorFactory = new PhpFileExtractorFactory($this->getDocParser());

        return $phpFileExtractorFactory->create();
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

        $lexer = new Lexer();
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createForVersion(PhpVersion::fromString('8.3'));
        $ast = $parser->parse(file_get_contents($file->getPathname()));

        $catalogue = new MessageCatalogue();
        $extractor->visitPhpFile($file, $catalogue, $ast);

        return $catalogue;
    }

    private function getDocParser()
    {
        $docParser = new DocParser();
        $docParser->setImports([
            'desc' => 'JMS\TranslationBundle\Annotation\Desc',
            'meaning' => 'JMS\TranslationBundle\Annotation\Meaning',
            'ignore' => 'JMS\TranslationBundle\Annotation\Ignore',
        ]);
        $docParser->setIgnoreNotImportedAnnotations(true);

        return $docParser;
    }
}
