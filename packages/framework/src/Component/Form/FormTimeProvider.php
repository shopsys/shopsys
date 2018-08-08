<?php

namespace Shopsys\FrameworkBundle\Component\Form;

use DateTime;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormTimeProvider
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $name
     */
    public function generateFormTime($name): \DateTime
    {
        $startTime = new DateTime();
        $key = $this->getSessionKey($name);
        $this->session->set($key, $startTime);
        return $startTime;
    }

    /**
     * @param string $name
     */
    public function isFormTimeValid($name, array $options): bool
    {
        $startTime = $this->findFormTime($name);

        if ($startTime === null) {
            return false;
        }

        if ($options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] !== null) {
            return new DateTime('-' . $options[TimedFormTypeExtension::OPTION_MINIMUM_SECONDS] . ' second') >= $startTime;
        }

        return true;
    }

    /**
     * @param string $name
     */
    public function hasFormTime($name): bool
    {
        $key = $this->getSessionKey($name);
        return $this->session->has($key);
    }

    /**
     * @param string $name
     */
    public function findFormTime($name): ?\DateTime
    {
        $key = $this->getSessionKey($name);
        if ($this->hasFormTime($name)) {
            return $this->session->get($key);
        }
        return null;
    }

    /**
     * @param string $name
     */
    public function removeFormTime($name)
    {
        $key = $this->getSessionKey($name);
        $this->session->remove($key);
    }

    /**
     * @param string $name
     */
    protected function getSessionKey($name): string
    {
        return 'timedSpam-' . $name;
    }
}
