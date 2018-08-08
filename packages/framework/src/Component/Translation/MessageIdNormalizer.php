<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;

class MessageIdNormalizer
{
    public function normalizeMessageId(string $messageId): string
    {
        return trim(preg_replace('~\s+~u', ' ', $messageId));
    }

    public function getNormalizedCatalogue(MessageCatalogue $catalogue): \JMS\TranslationBundle\Model\MessageCatalogue
    {
        $normalizedCatalogue = new MessageCatalogue();
        $normalizedCatalogue->setLocale($catalogue->getLocale());

        foreach ($catalogue->getDomains() as $domain => $messageCollection) {
            foreach ($messageCollection->all() as $message) {
                $normalizedMessage = $this->getNormalizedMessage($message, $domain);
                $normalizedCatalogue->add($normalizedMessage);
            }
        }

        return $normalizedCatalogue;
    }
    
    private function getNormalizedMessage(Message $message, string $domain): \JMS\TranslationBundle\Model\Message
    {
        $normalizedMessageId = $this->normalizeMessageId($message->getId());

        $normalizedMessage = new Message($normalizedMessageId, $domain);
        $normalizedMessage->setDesc($message->getDesc());
        $normalizedMessage->setLocaleString($message->getLocaleString());
        $normalizedMessage->setMeaning($message->getMeaning());
        $normalizedMessage->setNew($message->isNew());
        foreach ($message->getSources() as $source) {
            $normalizedMessage->addSource($source);
        }

        return $normalizedMessage;
    }
}
