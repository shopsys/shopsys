<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\GrapesJs;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Reference\W3CReference;

class GrapesJsParser
{
    protected const GJS_PRODUCTS_REGEX = '/<div[^<>]*class="gjs-products"[^<>]*data-products=".[^"]*"[^<>]*><\/div>|<div[^<>]*data-products=".*[^"]"[^<>]*class="gjs-products"[^<>]*><\/div>/';
    protected const GJS_PRODUCT_REGEX = '/<div[^<>]*class="gjs-product"[^<>]*data-product="[^"]*"[^<>]*><\/div>|<div[^<>]*data-product="[^"]*"[^<>]*class="gjs-product"[^<>]*><\/div>/';
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

        $newText = preg_replace_callback(static::GJS_PRODUCTS_REGEX, static function (array $matches): string {
            preg_match('/data-products="(.+?)"/', $matches[0], $productMatches);
            $productArray = explode(',', $productMatches[1]);
            $trimmedProductArray = array_map(static fn ($product) => trim($product), $productArray);
            $productCatnumsString = implode(',', $trimmedProductArray);

            return sprintf('%s[gjc-comp-ProductList=%s]%s', static::GJS_PRODUCTS_SEPARATOR, $productCatnumsString, static::GJS_PRODUCTS_SEPARATOR);
        }, preg_replace(static::GJS_PRODUCT_REGEX, '', $text));

        $sanitizer = $this->getConfiguredSanitizer();

        return $sanitizer->sanitize($newText);
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
            ->withMaxInputLength(25000);

        foreach (array_keys(W3CReference::HEAD_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        foreach (array_keys(W3CReference::BODY_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        return new HtmlSanitizer($config);
    }
}
