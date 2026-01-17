<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Xml;

use DOMDocument;

class XmlNormalizerHelper
{
    public function normalizeXml(string $content): string
    {
        $document = new DOMDocument('1.0');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $document->loadXML($content);

        return $document->saveXML();
    }
}
