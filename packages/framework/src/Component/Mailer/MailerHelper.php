<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Mailer;

class MailerHelper
{
    /**
     * @param string|null $string
     * @return string
     */
    public function escapeOptionalString(?string $string): string
    {
        if ($string === null) {
            return '-';
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }
}
