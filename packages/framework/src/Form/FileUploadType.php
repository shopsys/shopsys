<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class FileUploadType extends AbstractType
{
    public function __construct(
        private readonly UploadedFileFacade $uploadedFileFacade,
        private readonly FilesIdsToFilesTransformer $filesIdsToFilesTransformer,
        private readonly UploadedFileConfig $uploadedFileConfig,
        private readonly UploadedFileLocator $uploadedFileLocator,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['entity', 'file_entity_class', 'file_type'])
            ->setDefault('file_type', UploadedFileTypeConfig::DEFAULT_TYPE_NAME)
            ->setDefault('data_class', UploadedFileData::class)
            ->setAllowedTypes('entity', ['object', 'null'])
            ->setAllowedTypes('file_entity_class', 'string')
            ->setAllowedTypes('file_type', 'string');
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['files_by_id'] = $this->getFilesIndexedById($options);
        $view->vars['entity'] = $options['entity'];
        $view->vars['multiple'] = $this->isMultiple($options);
        $view->vars['requires_friendly_name'] = $this->isRequiredFriendlyName($options);
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        $builder
            ->add(
                $builder->create('orderedFiles', CollectionType::class, [
                    'required' => false,
                    'entry_type' => HiddenType::class,
                ])->addModelTransformer($this->filesIdsToFilesTransformer),
            )
            ->add(
                $builder->create('currentFilenamesIndexedById', CollectionType::class, [
                    'required' => false,
                    'entry_type' => TextType::class,
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\NotBlank(message: 'Please enter the filename'),
                            new Constraints\Length(
                                max: 245,
                                maxMessage: 'File name cannot be longer than {{ limit }} characters',
                            ),
                        ],
                    ],
                ]),
            )
            ->add('filesToDelete', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->getFilesIndexedById($options),
                'choice_label' => 'filename',
                'choice_value' => 'id',
            ])
            ->add('file', FileType::class, [
                'multiple' => $this->isMultiple($options),
                'mapped' => false,
            ])
            ->add(
                $builder->create('relations', FilesType::class, [
                    'multiple' => $this->isMultiple($options),
                    'constraints' => [
                        new Constraints\Callback(
                            callback: [$this, 'validateSelectedFiles'],
                            payload: $options['file_constraints'],
                        ),
                    ],
                ]),
            )
            ->add(
                $builder->create('relationsFilenames', CollectionType::class, [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\NotBlank(message: 'Please enter the filename'),
                            new Constraints\Length(
                                max: 255,
                                maxMessage: 'Name cannot be longer than {{ limit }} characters',
                            ),
                        ],
                    ],
                ]),
            );

        $this->buildLocalizedNamesFieldsIfNecessary($options, $builder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    private function getFilesIndexedById(array $options): array
    {
        if ($options['entity'] === null) {
            return [];
        }

        $uploadedFiles = $this->uploadedFileFacade->getUploadedFilesByEntity(
            $options['entity'],
            $options['file_type'],
        );

        $uploadedFilesIndexedById = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFilesIndexedById[$uploadedFile->getId()] = $uploadedFile;
        }

        return $uploadedFilesIndexedById;
    }

    private function isMultiple(array $options): bool
    {
        if ($options['file_entity_class'] === null) {
            return false;
        }

        $fileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass(
            $options['file_entity_class'],
        );
        $fileTypeConfig = $fileEntityConfig->getTypeByName($options['file_type']);

        return $fileTypeConfig->isMultiple();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]|null $selectedFiles
     * @param \Symfony\Component\Validator\Constraint[] $fileConstraints
     */
    public function validateSelectedFiles(
        ?array $selectedFiles,
        ExecutionContextInterface $context,
        array $fileConstraints,
    ): void {
        if ($selectedFiles === null || $fileConstraints === []) {
            return;
        }

        foreach ($selectedFiles as $selectedFile) {
            $filepath = $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($selectedFile);
            $file = new File($filepath, false);

            $validator = $context->getValidator();
            $violations = $validator->validate($file, $fileConstraints);

            foreach ($violations as $violation) {
                $context->addViolation($violation->getMessageTemplate(), $violation->getParameters());
            }
        }
    }

    private function isRequiredFriendlyName(array $options): bool
    {
        if ($options['file_entity_class'] === null) {
            return false;
        }

        $fileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass(
            $options['file_entity_class'],
        );

        return $fileEntityConfig->getTypeByName($options['file_type'])->isRequiredFriendlyName();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return AbstractFileUploadType::class;
    }

    private function buildLocalizedNamesFieldsIfNecessary(array $options, FormBuilderInterface $builder): void
    {
        if (!$this->isRequiredFriendlyName($options)) {
            return;
        }

        $namesOptions = [
            'required' => false,
            'entry_type' => LocalizedType::class,
            'allow_add' => true,
            'entry_options' => [
                'label' => '',
                'help' => t('Name in the corresponding locale must be filled-in in order to display the file on the storefront'),
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length([
                            'max' => 255,
                            'maxMessage' => 'Name cannot be longer than {{ limit }} characters',
                        ]),
                    ],
                ],
            ],
        ];

        $builder
            ->add('namesIndexedById', CollectionType::class, $namesOptions)
            ->add('names', CollectionType::class, $namesOptions)
            ->add('relationsNames', CollectionType::class, $namesOptions);
    }
}
