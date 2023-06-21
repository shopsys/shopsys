<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Flag;

use App\Component\Form\FormBuilderHelper;
use App\Model\Product\Flag\Flag;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class FlagFormType extends AbstractType
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(private FormBuilderHelper $formBuilderHelper)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', LocalizedFullWidthType::class, [
                'label' => t('Name'),
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter flag name in all languages']),
                        new Constraints\Length(
                            ['max' => 100, 'maxMessage' => 'Flag name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
                'render_form_row' => false,
            ]);

        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => t('Basic information'),
        ]);

        $builderBasicInformationGroup
            ->add('rgbColor', ColorPickerType::class, [
                'label' => t('Color'),
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter flag color']),
                    new Constraints\Length([
                        'max' => 7,
                        'maxMessage' => 'Flag color must be in valid hexadecimal code e.g. #3333ff',
                    ]),
                ],
            ])
            ->add('visible', YesNoType::class, [
                'required' => false,
                'label' => t('Display'),
            ]);

        $builder->add($builderBasicInformationGroup);

        if ($options['flag'] !== null) {
            $builderSeoInformationGroup = $builder->create('seoGroup', GroupType::class, [
                'label' => t('Seo'),
            ]);

            $builderSeoInformationGroup
                ->add('urls', UrlListType::class, [
                    'route_name' => 'front_flag_detail',
                    'entity_id' => $options['flag']->getId(),
                    'label' => t('URL addresses'),
                ]);

            $builder->add($builderSeoInformationGroup);
        }

        $builder->add('save', SubmitType::class);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['flag'])
            ->setAllowedTypes('flag', [Flag::class, 'null'])
            ->setDefaults([
                'data_class' => FlagData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
