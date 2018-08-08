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
    
    public function generateFormTime(string $name): \DateTime
    {
        $startTime = new DateTime();
        $key = $this->getSessionKey($name);
        $this->session->set($key, $startTime);
        return $startTime;
    }
    
    public function isFormTimeValid(string $name, array $options): bool
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
    
    public function hasFormTime(string $name): bool
    {
        $key = $this->getSessionKey($name);
        return $this->session->has($key);
    }
    
    public function findFormTime(string $name): ?\DateTime
    {
        $key = $this->getSessionKey($name);
        if ($this->hasFormTime($name)) {
            return $this->session->get($key);
        }
        return null;
    }
    
    public function removeFormTime(string $name): void
    {
        $key = $this->getSessionKey($name);
        $this->session->remove($key);
    }
    
    protected function getSessionKey(string $name): string
    {
        return 'timedSpam-' . $name;
    }
}
