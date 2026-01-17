<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CategorySeo;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReadyCategorySeoCombinationFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readyCategorySeoMix = $options['readyCategorySeoMix'];

        $builder
            ->add('urls', UrlListType::class, [
                'required' => true,
                'route_name' => 'front_category_seo',
                'entity_id' => $readyCategorySeoMix?->getId(),
                'label' => 'URL Settings',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('h1', TextType::class, [
                'label' => 'Heading (H1)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('showInCategory', YesNoType::class, [
                'label' => 'Show in the category',
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Short description of category',
                'required' => false,
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Category description',
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => 'Page title',
                'required' => false,
                'attr' => ['data-js-recommended-length' => 60],
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta description',
                'required' => false,
                'attr' => ['data-js-recommended-length' => 155],
            ])
            ->add('categorySeoFilterFormTypeAllQueriesJson', HiddenType::class)
            ->add('selectedCategorySeoMixCombinationJson', HiddenType::class)
            ->add('actionBar', ActionBarType::class, [
                'back_url' => $options['new_combination_url'],
                'back_label' => t('Back to overview of available combinations'),
                'save_label' => t('Save'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['readyCategorySeoMix', 'new_combination_url'])
            ->addAllowedTypes('readyCategorySeoMix', [ReadyCategorySeoMix::class, 'null'])
            ->addAllowedTypes('new_combination_url', 'string')
            ->setDefaults([
                'data_class' => ReadyCategorySeoMixData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
