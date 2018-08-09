<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleCheckboxChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
            'expanded' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        foreach ($builder->all() as $i => $child) {
            /* @var $child \Symfony\Component\Form\FormBuilderInterface */
            $options = $child->getOptions();
            $builder->remove($i);
            $options['required'] = false;
            $builder->add($i, CheckboxType::class, $options);
        }
    }
}
