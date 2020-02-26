<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Flag;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\Flag\FlagFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class FlagFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [
        'name',
    ];

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
        $builder->add('sale', CheckboxType::class, ['required' => false]);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FlagFormType::class;
    }
}
