<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InlineLabeledFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add($builder->getName(), $options['entry_type'], array_merge(
            $options['entry_options'],
            ['label' => false],
        ));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'entry_type' => TextType::class,
            'entry_options' => [],
        ]);

        $resolver->setAllowedTypes('entry_type', 'string');
        $resolver->setAllowedTypes('entry_options', 'array');
    }
}
