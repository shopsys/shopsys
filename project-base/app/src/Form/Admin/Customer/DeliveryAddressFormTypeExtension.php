<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Customer\DeliveryAddressFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class DeliveryAddressFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(private FormBuilderHelper $formBuilderHelper)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield DeliveryAddressFormType::class;
    }
}
