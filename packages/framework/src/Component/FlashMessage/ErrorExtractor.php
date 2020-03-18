<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class ErrorExtractor
{
    /**
     * @param \Symfony\Component\Form\Form $form
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBag $flashMessageBag
     * @return string[]
     */
    public function getAllErrorsAsArray(Form $form, FlashBag $flashMessageBag)
    {
        $errors = $flashMessageBag->get(FlashMessage::KEY_ERROR);
        foreach ($form->getErrors(true) as $error) {
            /* @var $error \Symfony\Component\Form\FormError */
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
