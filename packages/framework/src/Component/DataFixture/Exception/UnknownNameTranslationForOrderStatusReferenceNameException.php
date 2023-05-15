<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class UnknownNameTranslationForOrderStatusReferenceNameException extends Exception implements DataFixtureException
{
    /**
     * @param string $referenceName
     * @param \Exception|null $previous
     */
    public function __construct(string $referenceName, ?Exception $previous = null)
    {
        parent::__construct(
            sprintf('There is no name translation set for "%s" order status reference name.', $referenceName),
            0,
            $previous,
        );
    }
}
