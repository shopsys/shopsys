<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileExtractor;
use JMS\TranslationBundle\Translation\ExtractorManager;
use Override;
use Psr\Log\LoggerInterface;

class NormalizingExtractorManager extends ExtractorManager
{
    public function __construct(
        FileExtractor $extractor,
        LoggerInterface $logger,
        protected readonly MessageIdNormalizer $messageIdNormalizer,
    ) {
        parent::__construct($extractor, $logger);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extract(): MessageCatalogue
    {
        return $this->messageIdNormalizer->getNormalizedCatalogue(parent::extract());
    }
}
