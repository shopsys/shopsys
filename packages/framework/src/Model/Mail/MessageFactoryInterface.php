<?php

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MessageFactoryInterface
{
    /**
     * @param mixed $personalData
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $personalData);
}
