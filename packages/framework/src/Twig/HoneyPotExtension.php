<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig_Extension;
use Twig_SimpleFunction;

class HoneyPotExtension extends Twig_Extension
{
    const PASSWORD_FIELD_NAME = 'password';
    
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'formHoneyPotCheckPasswordAlreadyRendered',
                [$this, 'formHoneyPotCheckPasswordAlreadyRendered']
            ),
        ];
    }

    public function getName(): string
    {
        return 'honey_pot';
    }

    public function formHoneyPotCheckPasswordAlreadyRendered(FormView $formView)
    {
        $rootFormView = $this->getRootFormView($formView);

        if ($this->containsNotRenderedPassword($rootFormView)) {
            throw new \Shopsys\FrameworkBundle\Twig\Exception\HoneyPotRenderedBeforePasswordException();
        }
    }

    private function getRootFormView(FormView $formView): \Symfony\Component\Form\FormView
    {
        $rootFormView = $formView;

        while ($rootFormView->parent !== null) {
            $rootFormView = $rootFormView->parent;
        }

        return $rootFormView;
    }

    private function containsNotRenderedPassword(FormView $formView): bool
    {
        foreach ($formView->children as $childForm) {
            if (strpos($childForm->vars['name'], self::PASSWORD_FIELD_NAME) !== false && !$childForm->isRendered()) {
                return true;
            } elseif ($this->containsNotRenderedPassword($childForm)) {
                return true;
            }
        }

        return false;
    }
}
