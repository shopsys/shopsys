<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\Parameter as ParameterEntity;
use App\Model\Product\Parameter\Unit\CachedParameterUnit;

/**
 * Class CachedParameter
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameter extends ParameterEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    public function __construct(ParameterEntity $parameter)
    {
        parent::__construct(new ParameterData());

        $this->loadFromParent($parameter);
        $this->setCachedParameterTranslations($parameter);
        $this->setCachedParameterUnit($parameter);
        $this->setCachedParameterGroup($parameter);
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    private function setCachedParameterGroup(ParameterEntity $parameter): void
    {
        if ($parameter->getGroup() !== null) {
            $this->group = new CachedParameterGroup($parameter->getGroup());
        }
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    private function setCachedParameterUnit(ParameterEntity $parameter): void
    {
        if ($parameter->getParameterUnit() !== null) {
            $this->parameterUnit = new CachedParameterUnit($parameter->getParameterUnit());
        }
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     */
    private function setCachedParameterTranslations(ParameterEntity $parameter): void
    {
        //For purposes of using the cache, it is more economical to use the array instead of the object.
        //Cached objects will be used read-only.
        /** @var \Doctrine\Common\Collections\ArrayCollection $translations */
        $translations = [];

        //lazy load translation data
        $parameter->getTranslations();

        foreach ($parameter->translations as $translation) {
            $translations[] = new CachedParameterTranslation($translation);
        }

        $this->translations = $translations;
    }
}
