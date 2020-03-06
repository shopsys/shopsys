<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Exception;

use App\Component\Placeholder\PlaceholderInterface;

class PlaceholderAlreadyRegisteredException extends \Exception implements PlaceholderException
{
    /**
     * @param \App\Component\Placeholder\PlaceholderInterface $placeholder
     * @return \App\Component\Placeholder\Exception\PlaceholderAlreadyRegisteredException
     */
    public static function create(PlaceholderInterface $placeholder): self
    {
        return new self(
            sprintf(
                'Placeholder "%s" is already registered',
                $placeholder->getName()
            )
        );
    }
}
