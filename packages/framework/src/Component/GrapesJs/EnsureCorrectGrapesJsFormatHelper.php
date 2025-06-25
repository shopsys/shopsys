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
            return '<div class="gjs-text-ckeditor">' . t('Please replace this text with your own content.', locale: $locale) . '</div>';
        }

        if (str_starts_with(trim($string), '<div')) {
            return $string;
        }

        return '<div class="gjs-text-ckeditor">' . $string . '</div>';
    }
}
