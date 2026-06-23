<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transport;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class TransportGroupFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'label' => 'Name',
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter transport group name'),
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Transport group name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
            ])
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => 'Upload image',
                'image_entity_class' => TransportGroup::class,
                'file_constraints' => [
                    new Constraints\Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $options['transportGroup'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_crud_transport_group_list',
                'entity' => $options['transportGroup'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => TransportGroupData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setRequired(['transportGroup'])
            ->setAllowedTypes('transportGroup', [TransportGroup::class, 'null']);
    }
}
