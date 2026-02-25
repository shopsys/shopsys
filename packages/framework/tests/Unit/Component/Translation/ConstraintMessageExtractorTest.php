<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Translation\ConstraintMessageExtractor;
use Shopsys\FrameworkBundle\Component\Translation\PhpParserNodeHelper;
use SplFileInfo;

class ConstraintMessageExtractorTest extends TestCase
{
    public function testMessagesAreExtractedFromConstraintsWithNamedArguments(): void
    {
        $file = new SplFileInfo(__DIR__ . '/Resources/ConstraintUsageClass.php');

        $actualCatalogue = $this->extract($file);

        $this->assertCatalogueHasMessage($actualCatalogue, 'NotBlank message', 'validators', 13);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Length min message', 'validators', 15);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Length max message', 'validators', 16);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Length exact message', 'validators', 17);
        $this->assertCatalogueHasMessage($actualCatalogue, 'GreaterThan message', 'validators', 21);
    }

    public function testMessagesAreExtractedFromConstraintsWithPositionalArguments(): void
    {
        $file = new SplFileInfo(__DIR__ . '/Resources/ConstraintUsageClass.php');

        $actualCatalogue = $this->extract($file);

        $this->assertCatalogueHasMessage($actualCatalogue, 'Positional NotBlank message', 'validators', 27);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Positional Length min message', 'validators', 28);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Positional Length max message', 'validators', 28);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Positional GreaterThan message', 'validators', 29);
    }

    public function testMessagesAreExtractedFromConstraintsWithMixedArguments(): void
    {
        $file = new SplFileInfo(__DIR__ . '/Resources/ConstraintUsageClass.php');

        $actualCatalogue = $this->extract($file);

        $this->assertCatalogueHasMessage($actualCatalogue, 'Mixed Length min message', 'validators', 34);
        $this->assertCatalogueHasMessage($actualCatalogue, 'Mixed GreaterThan message', 'validators', 35);
    }

    public function testAllMessagesAreExtracted(): void
    {
        $file = new SplFileInfo(__DIR__ . '/Resources/ConstraintUsageClass.php');

        $actualCatalogue = $this->extract($file);

        $this->assertCount(11, $actualCatalogue->getDomain('validators')->all());
    }

    private function extract(SplFileInfo $file): MessageCatalogue
    {
        $phpParserNodeHelper = new PhpParserNodeHelper();
        $extractor = new ConstraintMessageExtractor($phpParserNodeHelper);

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createForVersion(PhpVersion::fromString('8.5'));
        $ast = $parser->parse(file_get_contents($file->getPathname()));

        $catalogue = new MessageCatalogue();
        $extractor->visitPhpFile($file, $catalogue, $ast);

        return $catalogue;
    }

    private function assertCatalogueHasMessage(
        MessageCatalogue $catalogue,
        string $messageId,
        string $domain,
        int $line,
    ): void {
        $this->assertTrue(
            $catalogue->has(new Message($messageId, $domain)),
            sprintf('Catalogue should have message "%s" in domain "%s"', $messageId, $domain),
        );

        $message = $catalogue->get($messageId, $domain);
        $sources = $message->getSources();

        $foundLine = false;

        foreach ($sources as $source) {
            if ($source instanceof FileSource && $source->getLine() === $line) {
                $foundLine = true;

                break;
            }
        }

        $this->assertTrue(
            $foundLine,
            sprintf('Message "%s" should have source at line %d', $messageId, $line),
        );
    }
}
