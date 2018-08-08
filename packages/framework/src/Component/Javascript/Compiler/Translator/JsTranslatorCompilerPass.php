<?php

namespace Shopsys\FrameworkBundle\Component\Javascript\Compiler\Translator;

use PLUG\JavaScript\JNodes\nonterminal\JProgramNode;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompilerPassInterface;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator\JsTranslatorCallParser;
use Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator\JsTranslatorCallParserFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class JsTranslatorCompilerPass implements JsCompilerPassInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator\JsTranslatorCallParser
     */
    private $jsTranslatorCallParser;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(
        JsTranslatorCallParser $jsTranslatorCallParser,
        Translator $translator
    ) {
        $this->jsTranslatorCallParser = $jsTranslatorCallParser;
        $this->translator = $translator;
    }

    public function process(JProgramNode $node)
    {
        $jsTranslatorsCalls = $this->jsTranslatorCallParser->parse($node);

        foreach ($jsTranslatorsCalls as $jsTranslatorsCall) {
            $messageIdArgumentNode = $jsTranslatorsCall->getMessageIdArgumentNode();

            // It is necessary to mark each part of pluralization with two hashes when the message is not translated.
            // Therefore custom method is used instead of using $this->translaor->trans method for transChoice calls.
            if ($jsTranslatorsCall->getFunctionName() === JsTranslatorCallParserFactory::METHOD_NAME_TRANS_CHOICE) {
                $translatedMessage = $this->translate($jsTranslatorsCall);
            } else {
                $translatedMessage = $this->translator->trans($jsTranslatorsCall->getMessageId());
            }

            $messageIdArgumentNode->terminate(json_encode($translatedMessage));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Javascript\Parser\Translator\JsTranslatorCall $jsTranslatorsCall
     * @return string
     */
    private function translate($jsTranslatorsCall)
    {
        $locale = $this->translator->getLocale();
        $catalogue = $this->translator->getCatalogue($locale);
        $messageId = $jsTranslatorsCall->getMessageId();
        $domain = $jsTranslatorsCall->getDomain();

        if ($catalogue->defines($messageId, $domain)) {
            return $catalogue->get((string)$messageId, $domain);
        } else {
            return (string)$messageId;
        }
    }
}
