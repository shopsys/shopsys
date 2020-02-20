<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Twig;

use App\Component\Placeholder\PlaceholderConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PlaceholdersExtension extends AbstractExtension
{
    /**
     * @var \App\Component\Placeholder\PlaceholderConverter
     */
    private $placeholderConverter;

    /**
     * @param \App\Component\Placeholder\PlaceholderConverter $placeholderConverter
     */
    public function __construct(PlaceholderConverter $placeholderConverter)
    {
        $this->placeholderConverter = $placeholderConverter;
    }

    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('placeholders', [$this, 'convert']),
        ];
    }

    /**
     * @param string|null $text
     * @param null|string[] $allowedPlaceholders
     * @return string|null
     */
    public function convert(?string $text = null, $allowedPlaceholders = null)
    {
        if (empty($text)) {
            return $text;
        }

        return $this->placeholderConverter->convert($text, $allowedPlaceholders);
    }
}
