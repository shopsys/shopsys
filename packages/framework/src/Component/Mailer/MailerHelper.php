<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Mailer;

class MailerHelper
{
    public function escapeString(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    public function escapeOptionalString(?string $string): string
    {
        if ($string === null) {
            return '-';
        }

        return $this->escapeString($string);
    }
}
