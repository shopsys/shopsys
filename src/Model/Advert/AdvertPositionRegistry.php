<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry as BaseAdvertPositionRegistry;

class AdvertPositionRegistry extends BaseAdvertPositionRegistry
{
    /**
     * @return string[]
     */
    public function getAllLabelsIndexedByNames(): array
    {
        $allLabelsIndexedByNames = parent::getAllLabelsIndexedByNames();
        $allLabelsIndexedByNames['cartPreview'] = t('nad souhrnem objednávky v košíku');

        return $allLabelsIndexedByNames;
    }
}
