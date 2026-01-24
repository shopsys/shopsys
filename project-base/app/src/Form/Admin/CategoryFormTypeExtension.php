<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Override;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class CategoryFormTypeExtension extends AbstractTypeExtension
{
    public const array DISABLED_FIELDS = [];

    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
