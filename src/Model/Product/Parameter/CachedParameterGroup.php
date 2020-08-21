<?php

declare(strict_types=1);


namespace App\Model\Product\Parameter;

use App\Model\LoadFromParentTrait;
use App\Model\Product\Parameter\ParameterGroup as ParameterGroupEntity;

/**
 * Class CachedParameterGroup
 *
 * Dummy data class for caching product filter config. Do not use It for persistence like entities.
 */
class CachedParameterGroup extends ParameterGroupEntity
{
    use LoadFromParentTrait;

    /**
     * @param \App\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    public function __construct(ParameterGroupEntity $parameterGroup)
    {
        parent::__construct(new ParameterGroupData());

        $this->loadFromParent($parameterGroup);
        $this->setCachedParameterTranslations($parameterGroup);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    private function setCachedParameterTranslations(ParameterGroupEntity $parameterGroup): void
    {
        //For purposes of using the cache, it is more economical to use the array instead of the object.
        //Cached objects will be used read-only.
        /** @var \Doctrine\Common\Collections\ArrayCollection $translations */
        $translations = [];

        //lazy load translation data
        $parameterGroup->getTranslations();

        foreach ($parameterGroup->translations as $translation) {
            $translations[] = new CachedParameterGroupTranslation($translation);
        }

        $this->translations = $translations;
    }
}
