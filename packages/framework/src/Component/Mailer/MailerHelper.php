<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Mailer;

class MailerHelper
{
    public function escapeOptionalString(?string $string): string
    {
        if ($string === null) {
            return '-';
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }
}
