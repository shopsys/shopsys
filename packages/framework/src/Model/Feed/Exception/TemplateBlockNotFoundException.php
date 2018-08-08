<?php

namespace Shopsys\FrameworkBundle\Model\Feed\Exception;

use Exception;

class TemplateBlockNotFoundException extends Exception implements FeedException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $blockName, string $templateName, Exception $previous = null)
    {
        $message = sprintf('Block "%s" does not exist in template "%s".', $blockName, $templateName);
        parent::__construct($message, 0, $previous);
    }
}
