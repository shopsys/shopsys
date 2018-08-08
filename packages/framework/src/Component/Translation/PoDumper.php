<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Dumper\DumperInterface;

class PoDumper implements DumperInterface
{
    public function dump(MessageCatalogue $catalogue, string $domain = 'messages'): string
    {
        $output = 'msgid ""' . "\n";
        $output .= 'msgstr ""' . "\n";
        $output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $output .= '"Language: ' . $catalogue->getLocale() . '\n"' . "\n";
        $output .= "\n";

        $messages = $catalogue->getDomain($domain)->all();
        $sortedMessages = $this->sortMessagesByMessageId($messages);

        foreach ($sortedMessages as $message) {
            $output .= sprintf('msgid "%s"' . "\n", $this->escape($message->getId()));
            if ($message->isNew()) {
                $output .= 'msgstr ""' . "\n";
            } else {
                $output .= sprintf('msgstr "%s"' . "\n", $this->escape($message->getLocaleString()));
            }

            $output .= "\n";
        }

        return $output;
    }
    
    private function escape(string $str): string
    {
        return addcslashes($str, "\0..\37\42\134");
    }

    /**
     * @param \JMS\TranslationBundle\Model\Message[] $messages
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    private function sortMessagesByMessageId(array $messages): array
    {
        usort($messages, function (Message $messageA, Message $messageB) {
            return strcmp($messageA->getId(), $messageB->getId());
        });

        return $messages;
    }
}
