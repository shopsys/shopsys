<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormTimeProvider
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function generateFormTime(string $name): DateTimeImmutable
    {
        $startTime = $this->clock->now();
        $key = $this->getSessionKey($name);
        $this->requestStack->getSession()->set($key, $startTime);

        return $startTime;
    }

    public function isFormTimeValid(string $name, array $options): bool
    {
        $startTime = $this->findFormTime($name);

        if ($startTime === null) {
            return false;
        }

        if ($options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] !== null) {
            return $this->clock->now()->modify(
                '-' . $options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] . ' second',
            ) >= $startTime;
        }

        return true;
    }

    public function hasFormTime(string $name): bool
    {
        $key = $this->getSessionKey($name);

        return $this->requestStack->getSession()->has($key);
    }

    public function findFormTime(string $name): ?DateTimeImmutable
    {
        $key = $this->getSessionKey($name);

        if ($this->hasFormTime($name)) {
            return $this->requestStack->getSession()->get($key);
        }

        return null;
    }

    public function removeFormTime(string $name): void
    {
        $key = $this->getSessionKey($name);
        $this->requestStack->getSession()->remove($key);
    }

    protected function getSessionKey(string $name): string
    {
        return 'timedSpam-' . $name;
    }
}
