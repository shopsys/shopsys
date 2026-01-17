<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Cache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Symfony\Component\Mime\Part\DataPart;

class MailEmbedCollector
{
    protected const NAMESPACE = 'mail_embeds_namespace';

    public function __construct(
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function addEmbed(MailEmbedData $mailEmbedData): string
    {
        try {
            $count = count($this->inMemoryCache->getValuesByNamespace(static::NAMESPACE));
        } catch (NamespaceCacheKeyNotFoundException) {
            $count = 0;
        }

        $key = 'embed' . $count;
        $this->inMemoryCache->save(static::NAMESPACE, $mailEmbedData, $key);

        return $key;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailEmbedData[]
     */
    protected function getEmbedsIndexedByKeys(): array
    {
        try {
            return $this->inMemoryCache->getValuesByNamespace(static::NAMESPACE);
        } catch (NamespaceCacheKeyNotFoundException) {
            return [];
        }
    }

    public function setEmbedsToMail(string $body, Email $email): string
    {
        foreach ($this->getEmbedsIndexedByKeys() as $mailEmbedData) {
            $body = $this->addEmbedDataToMail($mailEmbedData, $body, $email);
        }

        return $body;
    }

    protected function addEmbedDataToMail(MailEmbedData $mailEmbedData, string $body, Email $email): string
    {
        [, $base64] = explode(',', $mailEmbedData->embed, 2);
        $binary = base64_decode($base64, true);

        $part = (new DataPart($binary, $mailEmbedData->fileName, $mailEmbedData->contentType))->asInline();
        $contentId = $part->getContentId();

        $headers = $part->getHeaders();
        $headers->remove('Content-ID');
        $headers->addIdHeader('Content-ID', $contentId);
        $headers->addParameterizedHeader('Content-Disposition', 'inline', [
            'filename' => $mailEmbedData->fileName,
        ]);

        $email->addPart($part);

        return str_replace('cid:' . $mailEmbedData->fileName, 'cid:' . $contentId, $body);
    }
}
