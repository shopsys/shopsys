<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\UploadedFile;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Form\BasicFileUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Model\UploadedFile\UploadedFileFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class UploadedFileFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Filename',
            'constraints' => [
                new Constraints\NotBlank(['message' => 'Please enter the filename']),
                new Constraints\Length(
                    ['max' => 245, 'maxMessage' => 'File name cannot be longer than {{ limit }} characters'],
                ),
            ],
        ]);

        $builder->add('names', LocalizedType::class, [
            'required' => false,
            'label' => 'Names',
            'help' => t('Name in the corresponding locale must be filled-in in order to display the file on the storefront'),
            'entry_options' => [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        ['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters'],
                    ),
                ],
            ],
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_uploadedfile_list',
            'entity' => $options['uploaded_file'],
        ]);

        $builder->add('files', BasicFileUploadType::class, [
            'required' => false,
            'file_constraints' => [
                new Constraints\File([
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Uploaded file is to large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                ]),
            ],
            'label' => 'Replace file',
        ]);

        $builder->add('products', ProductsType::class, [
            'required' => false,
            'label_button_add' => t('Add to products'),
            'label' => 'Products',
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadedFileFormData::class,
        ]);

        $resolver
            ->setRequired('uploaded_file')
            ->setAllowedTypes('uploaded_file', [UploadedFile::class]);
    }
}
