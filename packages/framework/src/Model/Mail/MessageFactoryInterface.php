<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

interface MessageFactoryInterface
{
    public function createMessage(
        MailTemplate $template,
        mixed $data,
    ): MessageData;
}
