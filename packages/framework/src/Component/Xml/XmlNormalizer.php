<?php

namespace Shopsys\FrameworkBundle\Component\Xml;

use DOMDocument;

class XmlNormalizer
{
    /**
     * @param string $content
     * @return string|bool
     */
    public static function normalizeXml(string $content): string|bool
    {
        $document = new DOMDocument('1.0');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $document->loadXML($content);
        return $document->saveXML();
    }
}
