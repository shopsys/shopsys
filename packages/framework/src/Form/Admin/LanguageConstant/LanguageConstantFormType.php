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

class LanguageConstantFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData */
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
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_languageconstant_list',
                'save_label' => t('Save changes'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LanguageConstantData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
