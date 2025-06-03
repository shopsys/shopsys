<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\GrapesJs;

class EnsureCorrectGrapesJsFormatHelper
{
    /**
     * @param string|null $string
     * @param string $locale
     * @return string
     */
    public function ensureStringIsInCorrectGrapesJsFormat(
        ?string $string,
        string $locale,
    ): string {
        if ($string === null || trim(strip_tags($string)) === '') {
            $string = t('Please replace this text with your own content.', locale: $locale) . $string;
        }

        $isGrapeJsDivMissing = !str_contains('<div class="gjs-text-ckeditor">', $string);

        if ($isGrapeJsDivMissing) {
            $string = '<div class="gjs-text-ckeditor">' . $string . '</div>';
        }

        return $string;
    }
}
