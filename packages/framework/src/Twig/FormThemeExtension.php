<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig_SimpleFunction;

class FormThemeExtension extends \Twig_Extension
{
    const ADMIN_THEME = '@ShopsysFramework/Admin/Form/theme.html.twig';
    const FRONT_THEME = '@ShopsysShop/Front/Form/theme.html.twig';

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getDefaultFormTheme', [$this, 'getDefaultFormTheme']),
        ];
    }

    public function getDefaultFormTheme(): string
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if (mb_stripos($masterRequest->get('_controller'), 'Shopsys\FrameworkBundle\Controller\Admin') === 0) {
            return self::ADMIN_THEME;
        } else {
            return self::FRONT_THEME;
        }
    }

    public function getName(): string
    {
        return 'form_theme';
    }
}
