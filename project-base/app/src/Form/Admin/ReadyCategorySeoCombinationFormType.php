<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixDataForForm;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReadyCategorySeoCombinationFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readyCategorySeoMix = $options['readyCategorySeoMix'];

        $builder
            ->add('urls', UrlListType::class, [
                'required' => true,
                'route_name' => 'front_category_seo',
                'entity_id' => $readyCategorySeoMix !== null ? $readyCategorySeoMix->getId() : null,
                'label' => t('Nastavení URL'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('h1', TextType::class, [
                'label' => t('Nadpis H1'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('showInCategory', YesNoType::class, [
                'label' => t('Zobrazit v rozcestníku'),
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => t('Krátký popis kategorie'),
                'required' => false,
            ])
            ->add('description', CKEditorType::class, [
                'label' => t('Popis kategorie'),
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => t('Titulek stránky'),
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
                'label' => t('Uložit'),
                'attr' => [
                    'class' => 'margin-top-20',
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('readyCategorySeoMix')
            ->addAllowedTypes('readyCategorySeoMix', [ReadyCategorySeoMix::class, 'null'])
            ->setDefaults([
                'data_class' => ReadyCategorySeoMixDataForForm::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
