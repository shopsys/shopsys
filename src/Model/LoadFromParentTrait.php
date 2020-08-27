<?php

declare(strict_types=1);


namespace App\Model;

trait LoadFromParentTrait
{
    /**
     * @param mixed $parent
     */
    private function loadFromParent($parent): void
    {
        $objValues = get_object_vars($parent);
        foreach ($objValues as $key => $value) {
            //ignore
            //  __initializer__
            //  __cloner__
            //  __isInitialized__
            if (substr($key, 0, 2) === '__') {
                continue;
            }

            $this->$key = $value;
        }
    }
}
