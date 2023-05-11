<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormThemeExtension extends AbstractExtension
{
    protected const ADMIN_THEME = '@ShopsysFramework/Admin/Form/theme.html.twig';
    protected const FRONT_THEME = 'Front/Form/theme.html.twig';

    protected RequestStack $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getDefaultFormTheme', [$this, 'getDefaultFormTheme']),
        ];
    }

    /**
     * @return string
     */
    public function getDefaultFormTheme()
    {
        $masterRequest = $this->requestStack->getMainRequest();

        if ($this->isAdmin($masterRequest->get('_controller'))) {
            return static::ADMIN_THEME;
        }

        return static::FRONT_THEME;
    }

    /**
     * @param string $controller
     * @return bool
     */
    protected function isAdmin(string $controller): bool
    {
        return strpos($controller, 'Shopsys\FrameworkBundle\Controller\Admin') === 0 ||
            strpos($controller, 'App\Controller\Admin') === 0;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_theme';
    }
}
