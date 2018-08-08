<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmptyMessageChoiceTypeExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['empty_message'] = $options['empty_message'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('empty_message')
            ->setAllowedTypes('empty_message', 'string')
            ->setDefaults([
                'empty_message' => t('Nothing to choose from.'),
            ]);
    }

    public function getExtendedType()
    {
        return ChoiceType::class;
    }
}
