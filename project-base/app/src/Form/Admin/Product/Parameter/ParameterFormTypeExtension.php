<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Parameter;

use App\Component\Form\FormBuilderHelper;
use App\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ParameterFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(private FormBuilderHelper $formBuilderHelper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('visible');
        $builder->add('parameterType', ChoiceType::class, [
            'required' => false,
            'choices' => Parameter::PARAMETER_TYPES,
        ]);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ParameterFormType::class;
    }
}
