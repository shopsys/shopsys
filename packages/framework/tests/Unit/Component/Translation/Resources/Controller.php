<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation\Resources;

use JMS\TranslationBundle\Annotation\Ignore;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\Translator $translator
     */
    public function __construct(
        protected readonly Translator $translator,
    ) {
    }

    public function indexAction()
    {
        $this->translator->trans('trans test');
        $this->translator->trans('trans test with domain', [], 'testDomain');

        t('t test');
        t('t test with domain', [], 'testDomain');

        $locale = 'en';

        t('my %adjective% string', ['%adjective%' => 'awesome'], locale: $locale);
        t('my string with domain only', domain: 'another-translation-domain');
        t('my %adjective% string with domain', ['%adjective%' => 'awesome'], domain: 'another-translation-domain');
        t('my %adjective% string with named locale', ['%adjective%' => 'awesome'], 'someDomain', locale: $locale);
        t('my %adjective% string with unsorted arguments', ['%adjective%' => 'awesome'], locale: $locale, domain: 'unsortedDomain');
        t('my %adjective% string with null domain', ['%adjective%' => 'awesome'], null, $locale);

        /** @Ignore */
        t('ignored');
        /** @Ignore */
        $this->translator->trans('ignored');
    }
}
