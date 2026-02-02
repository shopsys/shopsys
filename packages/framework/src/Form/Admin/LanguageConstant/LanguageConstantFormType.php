<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\LanguageConstant;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class LanguageConstantFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData */
        $languageConstantData = $options['data'];

        $builder
            ->add('key', DisplayOnlyType::class, [
                'label' => 'Key',
                'data' => $languageConstantData->key,
            ])
            ->add('originalTranslation', DisplayOnlyType::class, [
                'label' => 'Original translation',
                'data' => $languageConstantData->originalTranslation,
            ])
            ->add('userTranslation', TextType::class, [
                'required' => true,
                'label' => 'User translation',
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_languageconstant_list',
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LanguageConstantData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
