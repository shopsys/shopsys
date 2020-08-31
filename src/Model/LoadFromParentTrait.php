<?php

declare(strict_types=1);


namespace App\Model;

use Doctrine\Persistence\Proxy;

trait LoadFromParentTrait
{
    /**
     * @param mixed $parent
     */
    private function loadFromParent($parent): void
    {
        if ($parent instanceof Proxy) {
            $parent->__load();
        }

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
