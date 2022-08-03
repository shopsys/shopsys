<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\Form\FormInterface;

class ErrorExtractor
{
    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param string[] $errorFlashMessages
     * @return string[]
     */
    public function getAllErrorsAsArray(FormInterface $form, array $errorFlashMessages): array
    {
        $errors = $errorFlashMessages;

        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
