<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Dumper\DumperInterface;
use Override;

class PoDumper implements DumperInterface
{
    /**
     * @param string $domain
     */
    #[Override]
    public function dump(MessageCatalogue $catalogue, $domain = Translator::DEFAULT_TRANSLATION_DOMAIN): string
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

    protected function escape(string $str): string
    {
        return addcslashes($str, "\0..\37\42\134");
    }

    /**
     * @param \JMS\TranslationBundle\Model\Message[] $messages
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    protected function sortMessagesByMessageId(array $messages): array
    {
        usort($messages, function (Message $messageA, Message $messageB) {
            return strcmp($messageA->getId(), $messageB->getId());
        });

        return $messages;
    }
}
