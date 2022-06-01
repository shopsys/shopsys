<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade;

class ContactFormMainTextResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade
     */
    protected ContactFormSettingsFacade $contactFormSettingsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\ContactForm\ContactFormSettingsFacade $contactFormSettingsFacade
     */
    public function __construct(Domain $domain, ContactFormSettingsFacade $contactFormSettingsFacade)
    {
        $this->domain = $domain;
        $this->contactFormSettingsFacade = $contactFormSettingsFacade;
    }

    /**
     * @return string
     */
    public function resolve(): string
    {
        return $this->contactFormSettingsFacade->getMainText($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'contactFormMainText'];
    }
}
