<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Parameter;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class ParameterFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(FormBuilderHelper $formBuilderHelper)
    {
        $this->formBuilderHelper = $formBuilderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('visible');

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes()
    {
        yield ParameterFormType::class;
    }
}
