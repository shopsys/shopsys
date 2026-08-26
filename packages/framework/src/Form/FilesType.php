<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class FilesType extends AbstractType
{
    public function __construct(
        protected readonly RouterInterface $router,
        protected readonly FilesIdsToFilesTransformer $filesIdsToFilesTransformer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->filesIdsToFilesTransformer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $multiple = $options['multiple'];
        $route = $multiple ? 'admin_filepicker_pickmultiple' : 'admin_filepicker_picksingle';

        $view->vars['label_button_add'] = $multiple ? t('Add files') : t('Select file');
        $view->vars['picker_url'] = $this->router->generate($route, ['jsInstanceId' => '__js_instance_id__']);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('multiple');
        $resolver->setAllowedTypes('multiple', 'bool');
        $resolver->setDefault('item_name', 'nameWithExtension');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return AbstractMultiplePickerType::class;
    }
}
