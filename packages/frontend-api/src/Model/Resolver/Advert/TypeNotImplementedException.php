<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use RuntimeException;

class TypeNotImplementedException extends RuntimeException
{
    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        parent::__construct(sprintf(
            'Advert type "%s" has not been implemented yet.',
            $type,
        ));
    }
}
