<?php

declare(strict_types=1);

namespace App\Component\Placeholder\Exception;

use App\Component\Placeholder\PlaceholderInterface;

class PlaceholderConversionErrorException extends \Exception implements PlaceholderException
{
    public const MAX_TEXT_LENGTH = 255;

    /**
     * @param \App\Component\Placeholder\PlaceholderInterface $placeholder
     * @param mixed $text
     * @return \App\Component\Placeholder\Exception\PlaceholderConversionErrorException
     */
    public static function create(PlaceholderInterface $placeholder, $text): self
    {
        return new self(
            sprintf(
                'Conversion of placeholder "%s" with pattern "%s" fail for text "%s..."',
                $placeholder->getName(),
                $placeholder->getPattern(),
                mb_substr($text, 0, self::MAX_TEXT_LENGTH)
            )
        );
    }
}
