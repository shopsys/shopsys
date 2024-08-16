<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint\Status;

use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ComplaintStatusFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter complaint status name in all languages']),
                        new Constraints\Length(
                            ['max' => 255, 'maxMessage' => 'Status name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComplaintStatusData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
