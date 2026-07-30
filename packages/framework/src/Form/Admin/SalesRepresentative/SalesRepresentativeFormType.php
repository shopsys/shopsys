<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\SalesRepresentative;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\PhoneType;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class SalesRepresentativeFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $salesRepresentative = $options['salesRepresentative'];

        if ($salesRepresentative instanceof SalesRepresentative) {
            $builderSystemDataGroup = $builder->create('systemData', GroupType::class, [
                'label' => 'System data',
            ]);

            $builderSystemDataGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $salesRepresentative->getId(),
            ]);

            $builder
                ->add($builderSystemDataGroup);
        }

        $builderPersonalDataGroup = $builder->create('personalData', GroupType::class, [
            'label' => 'Personal data',
        ]);

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'First name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'First name',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Last name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Last name',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                    new Email(message: 'Please enter valid email'),
                ],
                'label' => 'Email',
            ])
            ->add('telephone', PhoneType::class, [
                'required' => false,
                'label' => 'Telephone',
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => SalesRepresentative::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                        extensions: [ImageProcessor::EXTENSION_JPG, ImageProcessor::EXTENSION_JPEG, ImageProcessor::EXTENSION_PNG],
                    ),
                ],
                'label' => 'Upload image',
                'entity' => $options['salesRepresentative'],
                'info_text' => t('You can upload following formats: PNG, JPG'),
            ]);

        $builder
            ->add($builderPersonalDataGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_salesrepresentative_list',
                'entity' => $options['salesRepresentative'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
