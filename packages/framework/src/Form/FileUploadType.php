<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class FileUploadType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer
     */
    private $filesIdsToFilesTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    private $uploadedFileConfig;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer $filesIdsToFilesTransformer
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     */
    public function __construct(
        UploadedFileFacade $uploadedFileFacade,
        FilesIdsToFilesTransformer $filesIdsToFilesTransformer,
        UploadedFileConfig $uploadedFileConfig
    ) {
        $this->uploadedFileFacade = $uploadedFileFacade;
        $this->filesIdsToFilesTransformer = $filesIdsToFilesTransformer;
        $this->uploadedFileConfig = $uploadedFileConfig;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
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

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['files_by_id'] = $this->getFilesIndexedById($options);
        $view->vars['entity'] = $options['entity'];
        $view->vars['multiple'] = $this->isMultiple($options);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetModelTransformers();

        $builder
            ->add(
                $builder->create('orderedFiles', CollectionType::class, [
                    'required' => false,
                    'entry_type' => HiddenType::class,
                ])->addModelTransformer($this->filesIdsToFilesTransformer)
            )
            ->add(
                $builder->create('currentFilenamesIndexedById', CollectionType::class, [
                    'required' => false,
                    'entry_type' => TextType::class,
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\NotBlank(['message' => 'Please enter the filename']),
                            new Constraints\Length(
                                ['max' => 245, 'maxMessage' => 'File name cannot be longer than {{ limit }} characters']
                            ),
                        ],
                    ],
                ])
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
            ]);
    }

    /**
     * @param array $options
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    private function getFilesIndexedById(array $options): array
    {
        if ($options['entity'] === null) {
            return [];
        }

        $uploadedFiles = $this->uploadedFileFacade->getUploadedFilesByEntity(
            $options['entity'],
            $options['file_type']
        );

        $uploadedFilesIndexedById = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $uploadedFilesIndexedById[$uploadedFile->getId()] = $uploadedFile;
        }

        return $uploadedFilesIndexedById;
    }

    /**
     * @param array $options
     * @return bool
     */
    private function isMultiple(array $options): bool
    {
        if ($options['file_entity_class'] === null) {
            return false;
        }

        $fileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfigByClass(
            $options['file_entity_class']
        );
        $fileTypeConfig = $fileEntityConfig->getTypeByName($options['file_type']);

        return $fileTypeConfig->isMultiple();
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return AbstractFileUploadType::class;
    }
}
