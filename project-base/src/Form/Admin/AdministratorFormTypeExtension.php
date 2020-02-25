<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdministratorFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class AdministratorFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield AdministratorFormType::class;
    }
}
