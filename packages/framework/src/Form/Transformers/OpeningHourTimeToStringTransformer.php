<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use DateTimeImmutable;
use Override;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Symfony\Component\Form\DataTransformerInterface;

class OpeningHourTimeToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        protected readonly DateTimeHelper $dateTimeHelper,
    ) {
    }

    /**
     * @param string|null $time
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
     */
    #[Override]
    public function reverseTransform($dateTime): ?string
    {
        return $dateTime?->format('H:i');
    }
}
