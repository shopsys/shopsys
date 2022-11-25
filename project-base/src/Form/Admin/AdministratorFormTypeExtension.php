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
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
