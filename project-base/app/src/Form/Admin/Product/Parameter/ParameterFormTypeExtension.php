<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Parameter;

use App\Model\Product\Parameter\ParameterGroupFacade;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ParameterFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ParameterFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
        private readonly ParameterGroupFacade $parameterGroupFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('visible');

        $builder->add('group', ChoiceType::class, [
            'placeholder' => t('-- Choose group --'),
            'required' => false,
            'choices' => $this->parameterGroupFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
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
