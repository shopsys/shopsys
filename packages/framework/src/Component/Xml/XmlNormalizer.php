<?php

namespace Shopsys\FrameworkBundle\Component\Xml;

use DOMDocument;

class XmlNormalizer
{
    /**
     * @param string $content
     */
    public static function normalizeXml($content): string
    {
        $document = new DOMDocument('1.0');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $document->loadXML($content);
        $generatedXml = $document->saveXML();

        return $generatedXml;
    }
}
