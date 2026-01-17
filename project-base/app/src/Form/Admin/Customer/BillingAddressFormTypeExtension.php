<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer;

use Override;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class BillingAddressFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [];

    public function __construct(
        private FormBuilderHelper $formBuilderHelper,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield BillingAddressFormType::class;
    }
}
