<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\Form\Form;

class ErrorService
{
    /**
     * @return string[]
     */
    public function getAllErrorsAsArray(Form $form, Bag $flashMessageBag): array
    {
        $errors = $flashMessageBag->getErrorMessages();
        foreach ($form->getErrors(true) as $error) {
            /* @var $error \Symfony\Component\Form\FormError */
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
