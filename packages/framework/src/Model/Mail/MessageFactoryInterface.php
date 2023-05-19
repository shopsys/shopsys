<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MessageFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param mixed $data
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $data);
}
