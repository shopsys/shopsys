<?php

declare(strict_types=1);

namespace App\Form\Admin\Store;

use App\Model\Store\Store;
use App\Model\Store\StoreData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class StoreFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['store'] instanceof Store) {
            $builder
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['store']->getId(),
                    'label' => t('ID'),
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $options['store']->isDefault() ? t('Yes') : t('No'),
                    'label' => t('Default store'),
                ]);
        }

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']
                    ),
                ],
                'label' => t('Name'),
            ])
            ->add('isEnabledOnDomains', DomainsType::class, [
                'required' => false,
                'label' => t('Display on'),
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'External ID cannot be longer than {{ limit }} characters']
                    ),
                ],
                'label' => t('External ID'),
            ])
            ->add('description', CKEditorType::class, [
                'required' => false,
            ])
            ->add('locationLatitude', NumberType::class, [
                'required' => false,
                'scale' => 8,
            ])
            ->add('locationLongitude', NumberType::class, [
                'required' => false,
                'scale' => 8,
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
            ])
            ->add('openingHours', TextareaType::class, [
                'required' => false,
            ])
            ->add('contactInfo', TextareaType::class, [
                'required' => false,
            ])
            ->add('specialMessage', TextareaType::class, [
                'required' => false,
            ])
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Store::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => t('Upload image'),
                'entity' => $options['store'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['store'])
            ->setAllowedTypes('store', [Store::class, 'null'])
            ->setDefaults([
                'data_class' => StoreData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
