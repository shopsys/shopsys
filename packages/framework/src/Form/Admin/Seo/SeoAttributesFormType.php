<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Seo;

use Override;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SeoAttributesFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $placeholderSourceAttributes = $options['placeholder_source_input_id'] !== null
            ? ['data-js-placeholder-source-input-id' => $options['placeholder_source_input_id']]
            : [];

        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'Page title',
                'attr' => ['data-js-recommended-length' => 60] + $placeholderSourceAttributes,
            ])
            ->add('metaDescription', TextareaType::class, [
                'required' => false,
                'label' => 'Meta description',
                'attr' => ['data-js-recommended-length' => 160],
            ])
            ->add('h1', TextType::class, [
                'required' => $options['h1_required'],
                'constraints' => $options['h1_required'] ? [new Constraints\NotBlank(message: 'Please enter heading (H1)')] : [],
                'label' => 'Heading (H1)',
                'attr' => $placeholderSourceAttributes,
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeoAttributesData::class,
            'label' => false,
            'placeholder_source_input_id' => null,
            'h1_required' => false,
        ]);
        $resolver->setAllowedTypes('placeholder_source_input_id', ['string', 'null']);
        $resolver->setAllowedTypes('h1_required', 'bool');
        $resolver->setInfo(
            'placeholder_source_input_id',
            'Id of the input holding the entity name — title and H1 placeholders mirror it',
        );
        $resolver->setInfo('h1_required', 'Makes the "Heading (H1)" field mandatory.');
    }
}
