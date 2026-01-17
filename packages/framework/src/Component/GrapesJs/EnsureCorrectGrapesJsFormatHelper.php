<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\GrapesJs;

class EnsureCorrectGrapesJsFormatHelper
{
    public function ensureStringIsInCorrectGrapesJsFormat(
        ?string $string,
        string $locale,
    ): string {
        if ($string === null || trim(strip_tags($string)) === '') {
            return '<div class="gjs-text-ckeditor">' . t('Please replace this text with your own content.', locale: $locale) . '</div>';
        }

        if ($this->containsGrapesJsComponents($string)) {
            return $string;
        }

        return '<div class="gjs-text-ckeditor">' . $string . '</div>';
    }

    protected function containsGrapesJsComponents(string $string): bool
    {
        // Check for any GrapesJS component class (gjs-*) or typed component (data-gjs-type)
        return (bool)preg_match('/class=["\'][^"\']*\bgjs-/', $string)
            || str_contains($string, 'data-gjs-type')
            || str_contains($string, 'data-gjs-droppable');
    }
}
