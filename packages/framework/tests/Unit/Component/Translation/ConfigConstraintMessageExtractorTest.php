<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Translation\ConfigConstraintMessageExtractor;
use SplFileInfo;

class ConfigConstraintMessageExtractorTest extends TestCase
{
    public function testMessagesAreExtractedFromConfigYamlFile(): void
    {
        $file = new SplFileInfo(__DIR__ . '/Resources/DummyMutation.types.yaml');

        $actualCatalogue = $this->extract($file);

        $expectedCatalogue = new MessageCatalogue();

        $message1 = new Message('Please enter email', 'customerValidators');
        $message2 = new Message('Please enter valid email', 'customerValidators');
        $message3 = new Message('Email cannot be longer than {{ limit }} characters', 'customerValidators');
        $expectedCatalogue->add($message1);
        $expectedCatalogue->add($message2);
        $expectedCatalogue->add($message3);

        $this->assertEquals($expectedCatalogue, $actualCatalogue);
    }

    private function extract(SplFileInfo $file): MessageCatalogue
    {
        $extractor = new ConfigConstraintMessageExtractor();

        $catalogue = new MessageCatalogue();
        $extractor->visitFile($file, $catalogue);

        return $catalogue;
    }
}
