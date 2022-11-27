<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Twig\Exception\HoneyPotRenderedBeforePasswordException;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HoneyPotExtension extends AbstractExtension
{
    protected const PASSWORD_FIELD_NAME = 'password';

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'formHoneyPotCheckPasswordAlreadyRendered',
                [$this, 'formHoneyPotCheckPasswordAlreadyRendered']
            ),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'honey_pot';
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     */
    public function formHoneyPotCheckPasswordAlreadyRendered(FormView $formView): void
    {
        $rootFormView = $this->getRootFormView($formView);

        if ($this->containsNotRenderedPassword($rootFormView)) {
            throw new HoneyPotRenderedBeforePasswordException();
        }
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @return \Symfony\Component\Form\FormView
     */
    protected function getRootFormView(FormView $formView): FormView
    {
        $rootFormView = $formView;

        while ($rootFormView->parent !== null) {
            $rootFormView = $rootFormView->parent;
        }

        return $rootFormView;
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @return bool
     */
    protected function containsNotRenderedPassword(FormView $formView): bool
    {
        foreach ($formView->children as $childForm) {
            if (strpos($childForm->vars['name'], static::PASSWORD_FIELD_NAME) !== false && !$childForm->isRendered()) {
                return true;
            }

            if ($this->containsNotRenderedPassword($childForm)) {
                return true;
            }
        }

        return false;
    }
}
