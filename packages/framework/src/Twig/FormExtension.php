<?php

namespace Shopsys\FrameworkBundle\Twig;

use Symfony\Component\Form\FormError;
use Twig_SimpleFunction;

class FormExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getFormErrorSourceId', [$this, 'getFormErrorSourceId']),
        ];
    }

    /**
     * Creates source id of FormError equally as it is created in JS function FpJsFormValidator.validate
     */
    public function getFormErrorSourceId(FormError $formError): string
    {
        $form = $formError->getOrigin();
        $sourceIdParts = [];
        do {
            $sourceIdParts[] = str_replace('_', '-', $form->getName());
            $form = $form->getParent();
        } while ($form !== null);

        return 'form-error-' . implode('-', array_reverse($sourceIdParts));
    }
}
