<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor;
use Shopsys\FrameworkBundle\Form\Constraints\FileAllowedExtension;
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
use Symfony\Component\Validator\Constraints as Assert;

class ImageUploadType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\ImagesIdsToImagesTransformer $imagesIdsToImagesTransformer
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     */
    public function __construct(
        private readonly ImageFacade $imageFacade,
        private readonly ImagesIdsToImagesTransformer $imagesIdsToImagesTransformer,
        private readonly ImageConfig $imageConfig,
    ) {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ImageUploadData::class,
            'entity' => null,
            'image_type' => null,
            'multiple' => null,
            'image_entity_class' => null,
            'extensions' => ImageProcessor::SUPPORTED_EXTENSIONS,
            'hide_delete_button' => false,
        ]);

        $resolver->setNormalizer(
            'file_constraints',
            function (Options $options, $fileConstraints) {
                if ($options['extensions'] === null || $options['extensions'] === []) {
                    return $fileConstraints;
                }

                return array_merge(
                    [
                        new FileAllowedExtension(['extensions' => $options['extensions']]),
                    ],
                    $fileConstraints,
                );
            },
        );
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['entity'] = $options['entity'];
        $view->vars['images_by_id'] = $this->getImagesIndexedById($options);
        $view->vars['image_type'] = $options['image_type'];
        $view->vars['multiple'] = $this->isMultiple($options);
        $view->vars['hide_delete_button'] = $options['hide_delete_button'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                            new Assert\Length([
                                'max' => 245,
                                'maxMessage' => 'File name cannot be longer than {{ limit }} characters',
                            ]),
                        ],
                    ],
                ],
            ])->add(
                $builder->create('namesIndexedByImageIdAndLocale', CollectionType::class, [
                    'required' => false,
                    'entry_type' => LocalizedType::class,
                    'entry_options' => [
                        'label' => '',
                        'entry_options' => [
                            'constraints' => [
                                new Assert\Length([
                                    'max' => 245,
                                    'maxMessage' => 'File name cannot be longer than {{ limit }} characters',
                                ]),
                            ],
                        ],
                    ],
                ]),
            );
    }

    /**
     * @param array $options
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    private function getImagesIndexedById(array $options)
    {
        if ($options['entity'] === null) {
            return [];
        }

        return $this->imageFacade->getImagesByEntityIndexedById($options['entity'], $options['image_type']);
    }

    /**
     * @param array $options
     * @return bool
     */
    private function isMultiple(array $options)
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
    public function getParent(): ?string
    {
        return AbstractFileUploadType::class;
    }
}
