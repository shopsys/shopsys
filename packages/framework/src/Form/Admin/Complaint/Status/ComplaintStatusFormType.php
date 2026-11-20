<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint\Status;

use Override;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ComplaintStatusFormType extends AbstractType
{
    public function __construct(private readonly ComplaintStatusTypeEnum $complaintStatusTypeEnum)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', LocalizedType::class, [
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(message: 'Please enter complaint status name in all languages'),
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Status name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
            ]);

        /** @var \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData */
        $complaintStatusData = $options['data'];

        if ($complaintStatusData->statusType !== ComplaintStatusTypeEnum::STATUS_TYPE_NEW) {
            $builder->add('statusType', ChoiceType::class, [
                'label' => t('Type'),
                'choices' => $this->complaintStatusTypeEnum->getAssignableIndexedByTranslations(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComplaintStatusData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
