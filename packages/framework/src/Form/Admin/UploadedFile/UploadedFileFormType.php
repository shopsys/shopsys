<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Form\BasicFileUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class UploadedFileFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => t('Filename'),
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter the filename']),
                new Constraints\Length(
                    ['max' => 245, 'maxMessage' => 'File name cannot be longer than {{ limit }} characters'],
                ),
            ],
        ]);

        $builder->add('names', LocalizedType::class, [
            'required' => false,
            'label' => t('Names'),
            'entry_options' => [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                    ),
                ],
            ],
        ]);

        $builder->add('save', SubmitType::class);

        $builder->add('files', BasicFileUploadType::class, [
            'required' => false,
            'file_constraints' => [
                new Constraints\File([
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Uploaded file is to large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                ]),
            ],
            'label' => t('Replace file'),
        ]);

        $builder->add('products', ProductsType::class, [
            'required' => false,
            'label_button_add' => t('Add to products'),
            'top_info_title' => t('Products'),
            'label' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UploadedFileFormData::class,
        ]);

        $resolver
            ->setRequired('uploaded_file')
            ->setAllowedTypes('uploaded_file', [UploadedFile::class]);
    }
}
