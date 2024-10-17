<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CategorySeo;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReadyCategorySeoCombinationFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readyCategorySeoMix = $options['readyCategorySeoMix'];

        $builder
            ->add('urls', UrlListType::class, [
                'required' => true,
                'route_name' => 'front_category_seo',
                'entity_id' => $readyCategorySeoMix?->getId(),
                'label' => t('URL Settings'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('h1', TextType::class, [
                'label' => t('Heading (H1)'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('showInCategory', YesNoType::class, [
                'label' => t('Show in the category'),
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => t('Short description of category'),
                'required' => false,
            ])
            ->add('description', CKEditorType::class, [
                'label' => t('Category description'),
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => t('Page title'),
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 60,
                ],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => t('Meta description'),
                'required' => false,
                'macro' => [
                    'name' => 'seoFormRowMacros',
                    'recommended_length' => 155,
                ],
            ])
            ->add('categorySeoFilterFormTypeAllQueriesJson', HiddenType::class)
            ->add('choseCategorySeoMixCombinationJson', HiddenType::class)
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => [
                    'class' => 'margin-top-20',
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('readyCategorySeoMix')
            ->addAllowedTypes('readyCategorySeoMix', [ReadyCategorySeoMix::class, 'null'])
            ->setDefaults([
                'data_class' => ReadyCategorySeoMixData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
