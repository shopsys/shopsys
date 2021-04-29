<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Transport\Type\TransportType;
use App\Model\Transport\Type\TransportTypeData;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class TransportTypeFormType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transportType = $options['transport_type'];

        $builder
            ->add('code', DisplayOnlyType::class, [
                'label' => t('Code'),
                'data' => $transportType->getCode(),
            ])
            ->add('names', LocalizedFullWidthType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Transport type name cannot be longer than {{ limit }} characters']),
                    ],
                ],
                'label' => t('Name'),
                'render_form_row' => false,
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['transport_type'])
            ->addAllowedTypes('transport_type', [TransportType::class, 'null'])
            ->setDefaults([
                'data_class' => TransportTypeData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
