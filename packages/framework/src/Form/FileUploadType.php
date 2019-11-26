<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileUploadType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(UploadedFileFacade $uploadedFileFacade)
    {
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['entity', 'file_entity_class', 'file_type'])
            ->setDefault('file_type', UploadedFileTypeConfig::DEFAULT_TYPE_NAME)
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

        return $this->uploadedFileFacade->getUploadedFilesByEntity($options['entity'], $options['file_type']);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        $builder
            ->add('file', FileType::class, [
                'multiple' => false,
                'mapped' => false,
            ]);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return AbstractFileUploadType::class;
    }
}
