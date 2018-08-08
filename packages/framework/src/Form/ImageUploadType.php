<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Form\Transformers\ImagesIdsToImagesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageUploadType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\ImagesIdsToImagesTransformer
     */
    private $imagesIdsToImagesTransformer;

    public function __construct(ImageFacade $imageFacade, ImagesIdsToImagesTransformer $imagesIdsToImagesTransformer)
    {
        $this->imageFacade = $imageFacade;
        $this->imagesIdsToImagesTransformer = $imagesIdsToImagesTransformer;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImageUploadData::class,
            'entity' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entity'] = $options['entity'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['images_by_id'] = $this->getImagesIndexedById($options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        if ($options['multiple']) {
            $builder->add(
                $builder->create('orderedImages', CollectionType::class, [
                    'required' => false,
                    'entry_type' => HiddenType::class,
                ])->addModelTransformer($this->imagesIdsToImagesTransformer)
            );
            $builder->add('imagesToDelete', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->getImagesIndexedById($options),
                'choice_label' => 'filename',
                'choice_value' => 'id',
            ]);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    private function getImagesIndexedById(array $options): array
    {
        if ($options['entity'] === null) {
            return [];
        }

        return $this->imageFacade->getImagesByEntityIndexedById($options['entity'], null);
    }

    public function getParent(): string
    {
        return FileUploadType::class;
    }
}
