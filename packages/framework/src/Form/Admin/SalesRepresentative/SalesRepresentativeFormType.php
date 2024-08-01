<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\SalesRepresentative;

use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SalesRepresentativeFormType extends AbstractType
{
    private ?SalesRepresentative $salesRepresentative = null;

    public function __construct()
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->salesRepresentative = $options['salesRepresentative'];

        if ($this->salesRepresentative instanceof SalesRepresentative) {
            $builderSystemDataGroup = $builder->create('systemData', GroupType::class, [
                'label' => t('System data'),
            ]);

            $builderSystemDataGroup->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $this->salesRepresentative->getId(),
            ]);

            $builder
                ->add($builderSystemDataGroup);
        }

        $builderPersonalDataGroup = $builder->create('personalData', GroupType::class, [
            'label' => t('Personal data'),
        ]);

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('First name'),
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter last name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Last name'),
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Email cannot be longer than {{ limit }} characters',
                    ]),
                    new Email(['message' => 'Please enter valid email']),
                ],
                'label' => t('Email'),
            ])
            ->add('telephone', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter telephone number']),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Telephone'),
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => t('Image'),
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => SalesRepresentative::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg'],
                        'mimeTypesMessage' => 'Image can be only in JPG or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => t('Upload image'),
                'entity' => $options['salesRepresentative'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
                'extensions' => [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
            ]);

        $builder
            ->add($builderPersonalDataGroup)
            ->add($builderImageGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['salesRepresentative'])
            ->setAllowedTypes('salesRepresentative', [SalesRepresentative::class, 'null'])
            ->setDefaults([
                'data_class' => SalesRepresentativeData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'salesRepresentative' => null,
            ]);
    }
}
