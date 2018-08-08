<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MessageFactoryInterface
{
    /**
     * @param mixed $personalData
     */
    public function createMessage(MailTemplate $template, $personalData): \Shopsys\FrameworkBundle\Model\Mail\MessageData;
}
