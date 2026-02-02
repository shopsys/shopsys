<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SingleCheckboxChoiceType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => false,
            'expanded' => true,
        ]);
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        foreach ($builder->all() as $i => $child) {
            $options = $child->getOptions();
            $builder->remove($i);
            $options['required'] = false;
            $builder->add($i, CheckboxType::class, $options);
        }
    }
}
