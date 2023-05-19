<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Translation\Resources;

use JMS\TranslationBundle\Annotation\Ignore;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    public function indexAction()
    {
        /** @var \Shopsys\FrameworkBundle\Component\Translation\Translator $translator */
        $translator = $this->get(Translator::class);

        $translator->trans('trans test');
        $translator->trans('trans test with domain', [], 'testDomain');

        t('t test');
        t('t test with domain', [], 'testDomain');

        /** @Ignore */
        t('ignored');
        /** @Ignore */
        $translator->trans('ignored');
    }
}
