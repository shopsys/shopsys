<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplayOnlyType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['widget_form_type'] = $options['widget_form_type'];
        $view->vars['data'] = $options['widget_form_type_data'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $widgetFormType = $options['widget_form_type'];
        
        if ($widgetFormType !== null) {
            $fieldName = $builder->getForm()->getName();

            $builder->add($fieldName, $widgetFormType, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'readonly' => 'readonly',
                ],
                'data' => $options['widget_form_type_data'],
            ]);
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['widget_form_type', 'widget_form_type_data'])
            ->setAllowedTypes('widget_form_type', ['string', 'null'])
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'readonly' => 'readonly',
                ],
                'widget_form_type' => null,
                'widget_form_type_data' => null,
            ]);
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return FormType::class;
    }
}
