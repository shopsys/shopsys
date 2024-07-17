<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicFileUploadType extends AbstractType implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['multiple'] = $options['multiple'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetModelTransformers();
        $builder->addModelTransformer($this);

        $builder
            ->add('uploadedFilenames', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
            ])
            ->add('file', FileType::class, [
                'multiple' => $options['multiple'],
                'mapped' => false,
            ]);
    }

    /**
     * @param array $value
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData
     */
    public function reverseTransform($value)
    {
        $uploadedFileData = $this->uploadedFileDataFactory->create();

        foreach ($value as $field => $fieldValue) {
            $uploadedFileData->{$field} = $fieldValue;
        }

        return $uploadedFileData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $value
     * @return array
     */
    public function transform($value): array
    {
        return (array)$value;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return AbstractFileUploadType::class;
    }
}
