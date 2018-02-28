<?php

namespace Shopsys\ShopBundle\Model\Gdpr\Mail;

use Shopsys\ShopBundle\Model\Mail\MailTemplate;
use Shopsys\ShopBundle\Model\Mail\MailTypeInterface;
use Shopsys\ShopBundle\Model\Mail\MessageData;
use Shopsys\ShopBundle\Model\Mail\MessageFactoryInterface;

class CredentialsRequestMail implements MailTypeInterface, MessageFactoryInterface
{
    const VARIABLE_EMAIL = '{e-mail}';
    const VARIABLE_URL = '{url}';
    const VARIABLE_DOMAIN = '{domain}';

    /**
     * @return string[]
     */
    public function getSubjectVariables()
    {
        return $this->getBodyVariables();
    }

    /**
     * @return string[]
     */
    public function getBodyVariables()
    {
        return [
            self::VARIABLE_URL,
            self::VARIABLE_EMAIL,
            self::VARIABLE_DOMAIN,
        ];
    }

    /**
     * @return string[]
     */
    public function getRequiredSubjectVariables()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getRequiredBodyVariables()
    {
        return [
           self::VARIABLE_URL,
        ];
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Mail\MailTemplate $template
     * @param mixed $user
     * @return \Shopsys\ShopBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $user)
    {
        return new MessageData(
            $user->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $user->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $user->getDomainId()),
            $this->getBodyValuesIndexedByVariableName($user),
            $this->getSubjectValuesIndexedByVariableName($user)
        );
    }
}
