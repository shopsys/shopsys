<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\ColorPickerType;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ParameterValueFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('rgbHex', ColorPickerType::class, [
            'required' => false,
            'label' => 'RGB Hex',
        ])->add('colorIcon', FileUploadType::class, [
            'label' => 'Color pattern icon',
            'info_text' => t('The icon takes precedence over the RGB color value. You can upload a PNG, JPG, GIF, or SVG file.'),
            'required' => false,
            'file_constraints' => [
                new Constraints\File(
                    maxSize: '2M',
                    maxSizeMessage: 'Uploaded file is too large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    extensions: [
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                    ],
                ),
            ],
            'entity' => $options['entity'],
            'file_entity_class' => ParameterValue::class,
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_parametervalue_list',
            'entity' => $options['entity'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['entity'])
            ->setAllowedTypes('entity', ParameterValue::class)
            ->setDefaults([
                'data_class' => ParameterValueData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
