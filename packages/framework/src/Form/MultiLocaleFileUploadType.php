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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->create('namesIndexedById', CollectionType::class, [
                'required' => false,
                'entry_type' => LocalizedType::class,
                'allow_add' => true,
                'entry_options' => [
                    'label' => '',
                    'entry_options' => [
                        'constraints' => [
                            new Constraints\Length([
                                'max' => 245,
                                'maxMessage' => 'File name cannot be longer than {{ limit }} characters',
                            ]),
                        ],
                    ],
                ],
            ]))->add(
                $builder->create('names', CollectionType::class, [
                    'required' => false,
                    'entry_type' => LocalizedType::class,
                    'allow_add' => true,
                    'entry_options' => [
                        'label' => '',
                        'entry_options' => [
                            'constraints' => [
                                new Constraints\Length([
                                    'max' => 245,
                                    'maxMessage' => 'File name cannot be longer than {{ limit }} characters',
                                ]),
                            ],
                        ],
                    ],
                ]),
            );
    }

    public function getParent()
    {
        return FileUploadType::class;
    }
}
