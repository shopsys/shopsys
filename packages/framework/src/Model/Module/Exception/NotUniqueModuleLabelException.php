<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module\Exception;

use Exception;

class NotUniqueModuleLabelException extends Exception
{
    /**
     * @param string[] $moduleLabelsIndexedByNames
     */
    public function __construct(array $moduleLabelsIndexedByNames, ?Exception $previous = null)
    {
        $moduleDescriptions = [];

        foreach ($moduleLabelsIndexedByNames as $moduleName => $moduleLabel) {
            $moduleDescriptions[] = sprintf('"%s" => "%s"', $moduleName, $moduleLabel);
        }

        parent::__construct('Module labels are not unique: ' . implode(', ', $moduleDescriptions), 0, $previous);
    }
}
