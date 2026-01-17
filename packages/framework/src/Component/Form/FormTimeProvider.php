<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormTimeProvider
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $name
     * @return \DateTimeImmutable
     */
    public function generateFormTime($name)
    {
        $startTime = $this->clock->now();
        $key = $this->getSessionKey($name);
        $this->requestStack->getSession()->set($key, $startTime);

        return $startTime;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isFormTimeValid($name, array $options)
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

    /**
     * @param string $name
     * @return bool
     */
    public function hasFormTime($name)
    {
        $key = $this->getSessionKey($name);

        return $this->requestStack->getSession()->has($key);
    }

    /**
     * @param string $name
     * @return \DateTimeImmutable|null
     */
    public function findFormTime($name)
    {
        $key = $this->getSessionKey($name);

        if ($this->hasFormTime($name)) {
            return $this->requestStack->getSession()->get($key);
        }

        return null;
    }

    /**
     * @param string $name
     */
    public function removeFormTime($name): void
    {
        $key = $this->getSessionKey($name);
        $this->requestStack->getSession()->remove($key);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getSessionKey($name)
    {
        return 'timedSpam-' . $name;
    }
}
