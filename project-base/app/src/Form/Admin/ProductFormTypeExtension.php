<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setSeoGroup($builder);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield ProductFormType::class;
    }
}
