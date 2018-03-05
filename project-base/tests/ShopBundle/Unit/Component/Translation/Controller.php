<?php

namespace Tests\ShopBundle\Unit\Component\Translation;

use JMS\TranslationBundle\Annotation\Ignore;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    public function indexAction()
    {
        $translator = $this->get(Translator::class);
        /* @var $translator \Shopsys\FrameworkBundle\Component\Translation\Translator */

        $translator->trans('trans test');
        $translator->transChoice('transChoice test', 5);
        $translator->trans('trans test with domain', [], 'testDomain');
        $translator->transChoice('transChoice test with domain', 5, [], 'testDomain');

        t('t test');
        tc('tc test', 5);
        t('t test with domain', [], 'testDomain');
        tc('tc test with domain', 5, [], 'testDomain');

        /** @Ignore */
        t('ignored');
        /** @Ignore */
        $translator->trans('ignored');
    }
}
