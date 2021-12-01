<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Tests\App\Test\FunctionalTestCase;

class TwigEnvironmentTest extends FunctionalTestCase
{
    /**
     * Public method TwigEnvironment::getFilter() is marked as internal
     * but we are using it in App/Twig/TranslationExtension anyway
     * so we need to know that the method is callable
     */
    public function testGetFilterMethodIsCallableOnTwigEnvironment()
    {
        $twigEnvironment = $this->getContainer()->get('twig');
        if (!is_callable([$twigEnvironment, 'getFilter'])) {
            $this->fail('Method "getFilter" is not callable on Twig\Environment class');
        }
    }
}
