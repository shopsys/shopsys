<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class FilesType extends AbstractType
{
    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        protected readonly RouterInterface $router,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['items'] = $form->getData();
        $view->vars['sortable'] = $options['sortable'];
        $view->vars['label_button_add'] = $options['label_button_add'];
        $view->vars['top_info_title'] = $options['top_info_title'];
        $view->vars['picker_url'] = $this->router->generate(
            'admin_filepicker_pickmultiple',
            ['jsInstanceId' => '__js_instance_id__'],
        );
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => HiddenType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'error_bubbling' => false,
            'sortable' => false,
            'label_button_add' => t('Add files'),
            'top_info_title' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
