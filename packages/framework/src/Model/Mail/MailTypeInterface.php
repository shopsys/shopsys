<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MailTypeInterface
{
    /**
     * @return string[]
     */
    public function getSubjectVariables(): array;

    /**
     * @return string[]
     */
    public function getBodyVariables(): array;

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables(): array;

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables(): array;
}
