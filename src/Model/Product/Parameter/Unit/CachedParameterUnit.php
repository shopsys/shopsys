<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter\Unit;

use App\Model\CachedTranslatableTrait;
use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\Unit\ParameterUnit as ParameterUnitEntity;

/**
 * Class CachedParameterUnit
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterUnit extends ParameterUnitEntity
{
    use LoadFromParentTrait;
    use CachedTranslatableTrait;

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnit $parameterUnit
     */
    public function __construct(ParameterUnitEntity $parameterUnit)
    {
        parent::__construct(new ParameterUnitData());

        $this->loadFromParent($parameterUnit);
        $this->setCachedParameterUnitTranslations($parameterUnit);
    }

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnit $parameterUnit
     */
    private function setCachedParameterUnitTranslations(ParameterUnitEntity $parameterUnit): void
    {
        //For purposes of using the cache, it is more economical to use the array instead of the object.
        //Cached objects will be used read-only.
        /** @var \Doctrine\Common\Collections\ArrayCollection $translations */
        $translations = [];

        //lazy load translation data
        $parameterUnit->getTranslations();

        foreach ($parameterUnit->translations as $translation) {
            $translations[] = new CachedParameterUnitTranslation($translation);
        }

        $this->translations = $translations;
    }
}
