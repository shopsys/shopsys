<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FlashMessage;

use Symfony\Component\Form\FormInterface;

class ErrorExtractor
{
    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param mixed[] $errorFlashMessages
     * @return string[]
     */
    public function getAllErrorsAsArray(FormInterface $form, array $errorFlashMessages): array
    {
        $errors = $errorFlashMessages;

        /** @var \Symfony\Component\Form\FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }
}
