<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

final class MultiLocaleFileUploadType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $namesOptions = [
            'required' => false,
            'entry_type' => LocalizedType::class,
            'allow_add' => true,
            'entry_options' => [
                'label' => '',
                'help' => t('Name in the corresponding locale must be filled-in in order to display the file on the storefront'),
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

    #[Override]
    public function getParent(): string
    {
        return FileUploadType::class;
    }
}
