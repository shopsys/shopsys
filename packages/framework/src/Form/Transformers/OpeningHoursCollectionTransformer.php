<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataHelper;
use Symfony\Component\Form\DataTransformerInterface;

class OpeningHoursCollectionTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $value
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[][]
     */
    public function transform($value)
    {
        return OpeningHoursDataHelper::getOpeningHoursIndexedByDayNumber($value);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[][] $value
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public function reverseTransform($value)
    {
        return OpeningHoursDataHelper::flattenOpeningHoursData($value);
    }
}
