<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\GrapesJs;

use DOMText;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Reference\W3CReference;

class GrapesJsParser
{
    protected const GJS_PRODUCTS_SEPARATOR = '|||';

    /**
     * @param string|null $text
     * @return string|null
     */
    public function parse(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $crawler = new Crawler($text);

        $crawler->filter('.gjs-product')->each(function (Crawler $node) {
            foreach ($node as $domElement) {
                $domElement->parentNode->removeChild($domElement);
            }
        });

        $crawler->filter('.gjs-products')->each(function (Crawler $node) {
            $dataProducts = $node->attr('data-products');

            if ($dataProducts === null) {
                return;
            }

            $productArray = explode(',', $dataProducts);
            $trimmedProductArray = array_map('trim', $productArray);
            $productCatnumsString = implode(',', $trimmedProductArray);

            foreach ($node as $domElement) {
                $domElement->parentNode->replaceChild(new DOMText(static::GJS_PRODUCTS_SEPARATOR . '[gjc-comp-ProductList=' . $productCatnumsString . ']' . static::GJS_PRODUCTS_SEPARATOR), $domElement);
            }
        });

        try {
            $newText = str_replace(['<body>', '</body>'], '', $crawler->html());
        } catch (InvalidArgumentException) {
            return null;
        }

        return $this->getConfiguredSanitizer()->sanitize($newText);
    }

    /**
     * @return \Symfony\Component\HtmlSanitizer\HtmlSanitizer
     */
    protected function getConfiguredSanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowStaticElements()
            ->allowRelativeLinks()
            ->allowElement('iframe', '*')
            ->withMaxInputLength(-1);

        foreach (array_keys(W3CReference::HEAD_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        foreach (array_keys(W3CReference::BODY_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        return new HtmlSanitizer($config);
    }
}
