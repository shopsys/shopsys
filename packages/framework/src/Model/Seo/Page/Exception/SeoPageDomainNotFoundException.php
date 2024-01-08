<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page\Exception;

use Exception;

class SeoPageDomainNotFoundException extends Exception implements SeoPageExceptionInterface
{
    /**
     * @param int $seoPageId
     * @param int $domainId
     * @param \Exception|null $previous
     */
    public function __construct(
        int $seoPageId,
        int $domainId,
        ?Exception $previous = null,
    ) {
        $message = sprintf(
            'SeoPageDomain for seo page with ID %d and domain ID %d not found.',
            $seoPageId,
            $domainId,
        );

        parent::__construct($message, 0, $previous);
    }
}
