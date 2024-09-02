<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class MultiLocaleFileUploadType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $namesOptions = [
            'required' => false,
            'entry_type' => LocalizedType::class,
            'allow_add' => true,
            'entry_options' => [
                'label' => '',
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t(
                        'Name in the corresponding locale must be filled-in in order to display the file on the storefront',
                    ),
                    'iconPlacement' => 'right',
                ],
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length([
                            'max' => 255,
                            'maxMessage' => 'Name cannot be longer than {{ limit }} characters',
                        ]),
                    ],
                ],
            ],
        ];

        $builder
            ->add($builder->create('namesIndexedById', CollectionType::class, $namesOptions))
            ->add($builder->create('names', CollectionType::class, $namesOptions))
            ->add($builder->create('relationsNames', CollectionType::class, $namesOptions));
    }

    public function getParent()
    {
        return FileUploadType::class;
    }
}
