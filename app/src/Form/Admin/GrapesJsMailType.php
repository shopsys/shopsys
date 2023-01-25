<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrapesJsMailType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['body_variables'] = $options['body_variables'];

        parent::buildView($view, $form, $options);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return TextareaType::class;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['body_variables'])
            ->setAllowedTypes('body_variables', 'array')
            ->setDefaults([
                'body_variables' => [],
                'entry_options' => [
                    'attr' => [
                        'class' => 'js-grapesjs-mail_textarea',
                    ],
                ],
            ]);
    }
}
