<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception;

use Exception;

class UnsupportedContentDocumentVersionException extends Exception
{
    public function __construct(mixed $version)
    {
        parent::__construct(sprintf(
            'Content document version "%s" is not supported',
            is_scalar($version) ? (string)$version : get_debug_type($version),
        ));
    }
}
