<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(private FormBuilderHelper $formBuilderHelper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CustomerUserFormType::class;
    }
}
