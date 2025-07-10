<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use DateTimeImmutable;
use Override;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Symfony\Component\Form\DataTransformerInterface;

class OpeningHourTimeToStringTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(
        protected readonly DateTimeHelper $dateTimeHelper,
    ) {
    }

    /**
     * @param string|null $time
     * @return \DateTimeImmutable|null
     */
    #[Override]
    public function transform($time): ?DateTimeImmutable
    {
        if ($time === null) {
            return null;
        }

        return $this->dateTimeHelper->createDateTimeFromTime($time);
    }

    /**
     * @param \DateTimeImmutable|null $dateTime
     * @return string|null
     */
    #[Override]
    public function reverseTransform($dateTime): ?string
    {
        return $dateTime?->format('H:i');
    }
}
