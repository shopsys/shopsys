<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value;

use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ParameterValueFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('rgbHex', TextType::class, [
            'required' => false,
            'label' => t('RGB Hex'),
            'constraints' => [
                new Constraints\Regex([
                    'pattern' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                    'message' => 'Entered RGB Hex is in invalid format. Valid formats are #336699 or #ABC.',
                ]),
            ],
        ])->add('colourIcon', FileUploadType::class, [
            'label' => t('Upload attachment'),
            'required' => false,
            'file_constraints' => [
                new Constraints\File([
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Uploaded file is to large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                ]),
            ],
            'entity' => $options['entity'],
            'file_entity_class' => ParameterValue::class,
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
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
