<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use DateTimeInterface;
use Override;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Form\DataTransformerInterface;

class EndOfDayTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform(mixed $value): ?DateTimeInterface
    {
        if (!$value instanceof DateTimeInterface) {
            return null;
        }

        return DatePoint::createFromInterface($value)->modify('-1 day');
    }

    #[Override]
    public function reverseTransform(mixed $value): ?DateTimeInterface
    {
        if (!$value instanceof DateTimeInterface) {
            return null;
        }

        return DatePoint::createFromInterface($value)->modify('+1 day');
    }
}
