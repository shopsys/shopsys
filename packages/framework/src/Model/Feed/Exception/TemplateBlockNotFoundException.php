<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class TemplateBlockNotFoundException extends Exception
{
    /**
     * @param string $blockName
     * @param string $templateName
     */
    public function __construct($blockName, $templateName, ?Exception $previous = null)
    {
        $message = sprintf('Block "%s" does not exist in template "%s".', $blockName, $templateName);

        parent::__construct($message, 0, $previous);
    }
}
