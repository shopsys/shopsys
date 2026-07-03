<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BasicFileUploadType extends AbstractType
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadedFileData::class,
            'multiple' => false,
            'with_names_inputs' => false,
        ]);
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['multiple'] = $options['multiple'];
        $view->vars['with_names_inputs'] = $options['with_names_inputs'];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        $builder->add('uploadedFilenames', CollectionType::class, [
            'entry_type' => $options['with_names_inputs'] ? TextType::class : HiddenType::class,
            'allow_add' => true,
        ]);

        if ($options['with_names_inputs']) {
            $builder->add('names', CollectionType::class, [
                'required' => false,
                'entry_type' => LocalizedType::class,
                'allow_add' => true,
                'entry_options' => [
                    'help' => t('Name in the corresponding locale must be filled-in in order to display the file on the storefront'),
                    'label' => '',
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\Length(
                                max: 255,
                                maxMessage: 'Name cannot be longer than {{ limit }} characters',
                            ),
                        ],
                    ],
                ],
            ]);
        }

        $builder->add('file', FileType::class, [
            'multiple' => $options['multiple'],
            'mapped' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return AbstractFileUploadType::class;
    }
}
