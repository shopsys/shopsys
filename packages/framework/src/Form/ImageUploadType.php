<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\Transformers\ImagesIdsToImagesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ImageUploadType extends AbstractType
{
    public function __construct(
        private readonly ImageFacade $imageFacade,
        private readonly ImagesIdsToImagesTransformer $imagesIdsToImagesTransformer,
        private readonly ImageConfig $imageConfig,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImageUploadData::class,
            'entity' => null,
            'image_type' => null,
            'multiple' => null,
            'image_entity_class' => null,
            'hide_delete_button' => false,
        ]);

        $resolver->setNormalizer(
            'file_constraints',
            function (Options $options, $fileConstraints) {
                // ensure the image format is always validated, unless the formats are restricted by an own File constraint
                if ($this->getExtensionsFromFileConstraints($fileConstraints) !== []) {
                    return $fileConstraints;
                }

                return array_merge(
                    [
                        new Constraints\File(extensions: ImageProcessor::SUPPORTED_EXTENSIONS),
                    ],
                    $fileConstraints,
                );
            },
        );
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entity'] = $options['entity'];
        $view->vars['images_by_id'] = $this->getImagesIndexedById($options);
        $view->vars['image_type'] = $options['image_type'];
        $view->vars['multiple'] = $this->isMultiple($options);
        $view->vars['hide_delete_button'] = $options['hide_delete_button'];
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        $builder->add(
            $builder->create('orderedImages', CollectionType::class, [
                'required' => false,
                'entry_type' => HiddenType::class,
            ])->addModelTransformer($this->imagesIdsToImagesTransformer),
        );
        $builder->add('imagesToDelete', ChoiceType::class, [
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->getImagesIndexedById($options),
            'choice_label' => 'filename',
            'choice_value' => 'id',
        ])
        ->add('file', FileType::class, [
            'multiple' => $this->isMultiple($options),
            'mapped' => false,
            'attr' => [
                'accept' => ImageProcessor::SUPPORTED_IMAGE_MIME_TYPES,
            ],
        ]);

        $builder
            ->add('uploadedFilenames', CollectionType::class, [
                'entry_type' => LocalizedType::class,
                'allow_add' => true,
                'entry_options' => [
                    'label' => '',
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\Length(
                                max: 245,
                                maxMessage: 'File name cannot be longer than {{ limit }} characters',
                            ),
                        ],
                    ],
                ],
            ])->add(
                $builder->create('namesIndexedByImageIdAndLocale', CollectionType::class, [
                    'required' => false,
                    'entry_type' => LocalizedType::class,
                    'label' => false,
                    'entry_options' => [
                        'label' => false,
                        'entry_options' => [
                            'constraints' => [
                                new Constraints\Length(
                                    max: 245,
                                    maxMessage: 'File name cannot be longer than {{ limit }} characters',
                                ),
                            ],
                        ],
                    ],
                ]),
            );
    }

    /**
     * @param array<\Symfony\Component\Validator\Constraint> $fileConstraints
     * @return array<int|string, string|array<string>>
     */
    private function getExtensionsFromFileConstraints(array $fileConstraints): array
    {
        foreach ($fileConstraints as $fileConstraint) {
            if ($fileConstraint instanceof Constraints\File && (array)$fileConstraint->extensions !== []) {
                return (array)$fileConstraint->extensions;
            }
        }

        return [];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    private function getImagesIndexedById(array $options): array
    {
        if ($options['entity'] === null) {
            return [];
        }

        return $this->imageFacade->getImagesByEntityIndexedById($options['entity'], $options['image_type']);
    }

    private function isMultiple(array $options): bool
    {
        if ($options['multiple'] !== null) {
            return $options['multiple'];
        }

        if ($options['image_entity_class'] === null) {
            return false;
        }

        $imageEntityConfig = $this->imageConfig->getImageEntityConfigByClass($options['image_entity_class']);

        return $imageEntityConfig->isMultiple($options['image_type']);
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
