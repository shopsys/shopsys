<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Media;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints\MustUploadFile;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class MediaFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = $options['media'] === null ? [new MustUploadFile(message: 'Choose image')] : [];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter title'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Title cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'label' => 'Upload image',
                'image_entity_class' => Media::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'constraints' => $imageConstraints,
                'entity' => $options['media'],
                'hide_delete_button' => true,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_crud_media_list',
                'entity' => $options['media'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => MediaData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setRequired(['media'])
            ->setAllowedTypes('media', [Media::class, 'null']);
    }
}
