<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileExtractor;
use JMS\TranslationBundle\Translation\ExtractorManager;
use Psr\Log\LoggerInterface;

class NormalizingExtractorManager extends ExtractorManager
{
    /**
     * @param \JMS\TranslationBundle\Translation\Extractor\FileExtractor $extractor
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Shopsys\FrameworkBundle\Component\Translation\MessageIdNormalizer $messageIdNormalizer
     */
    public function __construct(
        FileExtractor $extractor,
        LoggerInterface $logger,
        protected readonly MessageIdNormalizer $messageIdNormalizer
    ) {
        parent::__construct($extractor, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(): MessageCatalogue
    {
        return $this->messageIdNormalizer->getNormalizedCatalogue(parent::extract());
    }
}
