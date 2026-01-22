<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Flag;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class FlagFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'label' => 'Name',
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter flag name in all languages'),
                        new Constraints\Length(
                            max: 100,
                            maxMessage: 'Flag name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
            ]);

        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        $builderBasicInformationGroup
            ->add('rgbColor', ColorPickerType::class, [
                'label' => 'Color',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter flag color'),
                ],
            ])
            ->add('visible', YesNoType::class, [
                'label' => 'Display',
            ]);

        $builder->add($builderBasicInformationGroup);

        if ($options['flag'] !== null) {
            $builderSeoInformationGroup = $builder->create('seoGroup', GroupType::class, [
                'label' => 'Seo',
            ]);

            $builderSeoInformationGroup
                ->add('urls', UrlListType::class, [
                    'route_name' => 'front_flag_detail',
                    'entity_id' => $options['flag']->getId(),
                    'label' => 'URL addresses',
                ]);

            $builder->add($builderSeoInformationGroup);
        }

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_flag_list',
            'entity' => $options['flag'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
