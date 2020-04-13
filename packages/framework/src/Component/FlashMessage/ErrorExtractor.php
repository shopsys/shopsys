<?php

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\Form\Form;

class ErrorExtractor
{
    /**
     * @param \Symfony\Component\Form\Form $form
     * @param array $errorFlashMessages
     * @return string[]
     */
    public function getAllErrorsAsArray(Form $form, array $errorFlashMessages)
    {
        $errors = $errorFlashMessages;
        foreach ($form->getErrors(true) as $error) {
            /* @var $error \Symfony\Component\Form\FormError */
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
