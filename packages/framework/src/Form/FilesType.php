<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;

class FilesType extends AbstractType
{
    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Form\Transformers\FilesIdsToFilesTransformer $filesIdsToFilesTransformer
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly RouterInterface $router,
        protected readonly FilesIdsToFilesTransformer $filesIdsToFilesTransformer,
        protected readonly PropertyAccessorInterface $propertyAccessor,
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
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label_button_add' => t('Add files'),
            'picker_url' => $this->router->generate(
                'admin_filepicker_pickmultiple',
                ['jsInstanceId' => '__js_instance_id__'],
            ),
            'item_name' => 'nameWithExtension',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): ?string
    {
        return AbstractMultiplePickerType::class;
    }
}
