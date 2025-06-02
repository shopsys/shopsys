<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Styleguide;

use Shopsys\AdministrationBundle\Form\ColumnGroupType;
use Shopsys\AdministrationBundle\Form\RowGroupType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StyleguideFormType extends AbstractType
{

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $localizedGroup = $builder->create('localized', GroupType::class, ['label' => 'Localized']);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => TextType::class,
            'help' => 'This is help text',
            'disabled' => true,
            'entry_options' => [
                'required' => false,
            ],
            'label' => 'Localized: ' . TextType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => IntegerType::class,
            'help' => '<h3>This is a big step up</h3>
                    <p class="text-secondary">Lorem ipsum <a href="oreo">dolor sit amet</a>, consectetur adipisicing elit. Architecto at consectetur culpa ducimus eum fuga fugiat, ipsa iusto, modi nostrum recusandae reiciendis saepe.</p>',
            'help_html' => true,
            'main_constraints' => [
                new NotBlank(),
            ],
            'entry_options' => [
                'required' => false,
            ],
            'label' => 'Localized: ' . IntegerType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => TextareaType::class,
            'entry_options' => [
                'required' => false,
            ],
            'label' => 'Localized: ' . TextareaType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => MoneyType::class,
            'entry_options' => [
                'required' => false,
            ],
            'label' => 'Localized: ' . MoneyType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => ChoiceType::class,
            'layout' => LocalizedType::LAYOUT_BLOCK,
            'entry_options' => [
                'expanded' => true,
                'multiple' => true,
                'choices' => ['one' => 1, 'two' => 2],
                'required' => false,
            ],
            'label' => 'Localized: ' . ChoiceType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => ChoiceType::class,
            'layout' => LocalizedType::LAYOUT_BLOCK,
            'entry_options' => [
                'expanded' => true,
                'multiple' => false,
                'choices' => ['one' => 1, 'two' => 2],
                'required' => false,
            ],
            'label' => 'Localized: ' . ChoiceType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => ChoiceType::class,
            'layout' => LocalizedType::LAYOUT_BLOCK,
            'entry_options' => [
                'expanded' => false,
                'multiple' => true,
                'choices' => ['one' => 1, 'two' => 2],
                'required' => false,
            ],
            'label' => 'Localized: ' . ChoiceType::class,
        ]);

        $localizedGroup->add($this->getName(), LocalizedType::class, [
            'required' => false,
            'entry_type' => ChoiceType::class,
            'layout' => LocalizedType::LAYOUT_BLOCK,
            'entry_options' => [
                'expanded' => false,
                'multiple' => false,
                'choices' => ['one' => 1, 'two' => 2],
                'required' => false,
            ],
            'label' => 'Localized: ' . ChoiceType::class,
        ]);

         //$builder->add($localizedGroup);

        $rowGroup = $builder->create('row', RowGroupType::class);

        $column1 = $builder->create('column1', ColumnGroupType::class,
            [
                'label' => 'Column 1',
                'row_attr' => ['class' => 'col-md-6'],
            ],
        );

        $column1->add('firstName', TextType::class);
        $column1->add('lastName', TextType::class);

        $rowGroup->add($column1);

        $column2 = $builder->create('column2', ColumnGroupType::class,
            [
                'label' => 'Column 2',
                'row_attr' => ['class' => 'col-md-3'],
            ],
        );
        $column2->add('email', TextType::class);
        $column2->add('phone', TextType::class);

        $rowGroup->add($column2);

        $column3 = $builder->create('column3', ColumnGroupType::class,
            [
                'label' => 'Column 1',
                'row_attr' => ['class' => 'col-md-3'],
            ],
        );
        $column3->add('tutu', TextType::class);
        $column3->add('tata', TextType::class);

        $rowGroup->add($column3);

        $builder->add($rowGroup);

        $builder->add('text', TextType::class, [
            'required' => true,
            'label' => 'Text',
            'constraints' => [
                new NotBlank(),
            ],
            'help' => 'This is help text',
        ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return md5(uniqid('form', true));
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'attr' => ['novalidate' => 'novalidate'],
                ],
            );
    }
}
