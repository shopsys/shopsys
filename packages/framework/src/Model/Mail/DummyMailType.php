<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

class DummyMailType implements MailTypeInterface
{
    /**
     * @return string[]
     */
    public function getBodyVariables(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getSubjectVariables(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables(): array
    {
        return [];
    }
}
