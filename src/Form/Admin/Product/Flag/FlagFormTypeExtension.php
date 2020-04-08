<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Flag;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Product\Flag\FlagFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

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

        $builder->add('noticeLowPrice', TextType::class, [
            'required' => false,
            'label' => false,
            'constraints' => [
                new Constraints\Length(['max' => 8, 'maxMessage' => 'Text Hvězdičky pro nižší cenu nemůže být delší než {{ limit }} znaků']),
            ],
        ]);

        $builder->add('noticeHighPrice', TextType::class, [
            'required' => false,
            'label' => false,
            'constraints' => [
                new Constraints\Length(['max' => 8, 'maxMessage' => 'Text Hvězdičky pro vyšší cenu nemůže být delší než {{ limit }} znaků']),
            ],
        ]);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes()
    {
        yield FlagFormType::class;
    }
}
