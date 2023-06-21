<?php

declare(strict_types=1);

namespace App\Form\Admin\LanguageConstant;

use App\Model\LanguageConstant\LanguageConstantData;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class LanguageConstantFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\LanguageConstant\LanguageConstantData $languageConstantData */
        $languageConstantData = $options['data'];

        $builder
            ->add('key', DisplayOnlyType::class, [
                'label' => t('Key'),
                'data' => $languageConstantData->key,
            ])
            ->add('originalTranslation', DisplayOnlyType::class, [
                'label' => t('Original translation'),
                'data' => $languageConstantData->originalTranslation,
            ])
            ->add('userTranslation', TextType::class, [
                'required' => true,
                'label' => t('User translation'),
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
                'attr' => [
                    'class' => 'form-full__field__input',
                ],
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LanguageConstantData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
