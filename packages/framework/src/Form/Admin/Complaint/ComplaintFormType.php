<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\Complaint\Status\ComplaintItemsType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DisplayOnlyCustomerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyOrderType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class ComplaintFormType extends AbstractType
{
    protected const string VALIDATION_GROUP_TYPE_MONEY_RETURN = 'typeMoneyReturn';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly CountryFacade $countryFacade,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add($this->createBasicInformationGroup($builder, $options['complaint']));
        $builder->add($this->createDeliveryAddressGroup($builder));
        $builder->add('complaintItems', ComplaintItemsType::class, [
            'label' => false,
            'entry_type' => ComplaintItemFormType::class,
            'error_bubbling' => false,
            'allow_add' => false,
            'allow_delete' => false,
        ]);

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_complaint_list',
            'entity' => $options['complaint'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('complaint')
            ->setAllowedTypes('complaint', Complaint::class)
            ->setDefaults([
                'data_class' => ComplaintData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'constraints' => [new Constraints\Callback(callback: [$this, 'validateQuantityIsLessOrEqualThanOrdered'])],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

                    /** @var \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData */
                    $complaintData = $form->getData();

                    if ($complaintData->resolution === $this->complaintResolutionEnum::MONEY_RETURN) {
                        $validationGroups[] = static::VALIDATION_GROUP_TYPE_MONEY_RETURN;
                    }

                    return $validationGroups;
                },
            ]);
    }

    private function createBasicInformationGroup(
        FormBuilderInterface $builder,
        Complaint $complaint,
    ): FormBuilderInterface {
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        $builderBasicInformationGroup
            ->add('id', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $complaint->getId(),
            ]);

        if ($this->domain->isMultidomain()) {
            $builderBasicInformationGroup
                ->add('domainIcon', DisplayOnlyDomainIconType::class, [
                    'label' => 'Domain',
                    'data' => $complaint->getDomainId(),
                ]);
        }
        $builderBasicInformationGroup
            ->add('number', DisplayOnlyType::class, [
                'label' => 'Complaint number',
                'data' => $complaint->getNumber(),
            ])
            ->add('dateOfCreation', DisplayOnlyType::class, [
                'label' => 'Date of creation',
                'data' => $this->dateTimeFormatterExtension->formatDateTime($complaint->getCreatedAt()),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => $this->complaintStatusFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('resolution', ChoiceType::class, [
                'label' => 'Resolution',
                'required' => true,
                'choices' => $this->complaintResolutionEnum->getAllIndexedByTranslations(),
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'js-complaint-resolution',
                ],
            ])
            ->add('bankAccountNumber', TextType::class, [
                'label' => 'Bank account number',
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter bank account number',
                        groups: [static::VALIDATION_GROUP_TYPE_MONEY_RETURN],
                    ),
                    new Constraints\Length(
                        max: 34,
                        maxMessage: 'Bank account number cannot be longer than {{ limit }} characters',
                        groups: [static::VALIDATION_GROUP_TYPE_MONEY_RETURN],
                    ),
                    new Constraints\Regex(
                        pattern: '/^[a-zA-Z0-9\/\-]+$/',
                        message: 'Bank account number can contain only letters, numbers, slashes and dashes',
                        groups: [static::VALIDATION_GROUP_TYPE_MONEY_RETURN],
                    ),
                ],
                'row_attr' => [
                    'data-js-complaint-bank-account-number' => null,
                ],
            ])
            ->add('order', DisplayOnlyOrderType::class, [
                'label' => 'Order or document number',
                'order' => $complaint->getOrder(),
                'manualDocumentNumber' => $complaint->getManualDocumentNumber(),
            ])
            ->add('user', DisplayOnlyCustomerType::class, [
                'label' => 'Customer',
                'user' => $complaint->getCustomerUser(),
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter email'),
                    new Email(message: 'Please enter valid email'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                ],
            ]);

        return $builderBasicInformationGroup;
    }

    private function createDeliveryAddressGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDeliveryAddressGroup = $builder->create('deliveryAddressGroup', GroupType::class, [
            'label' => 'Delivery address',
        ]);

        $builderDeliveryAddressGroup
            ->add('deliveryFirstName', TextType::class, [
                'label' => 'First name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter first name of contact person'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'First name of contact person cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryLastName', TextType::class, [
                'label' => 'Last name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter last name of contact person'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Last name of contact person cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryCompanyName', TextType::class, [
                'label' => 'Company',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryTelephone', TextType::class, [
                'label' => 'Telephone',
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Telephone number cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryStreet', TextType::class, [
                'label' => 'Street',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter street'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => 'City',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter city'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'City name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryPostcode', TextType::class, [
                'label' => 'Postcode',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter zip code'),
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('deliveryCountry', ChoiceType::class, [
                'label' => 'Country',
                'required' => true,
                'choices' => $this->countryFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose country'),
                ],
            ]);

        return $builderDeliveryAddressGroup;
    }

    public function validateQuantityIsLessOrEqualThanOrdered(
        ComplaintData $complaintData,
        ExecutionContextInterface $context,
    ): void {
        foreach ($complaintData->complaintItems as $key => $complaintItemData) {
            $orderedQuantity = $complaintItemData->orderItem?->getQuantity();

            if ($complaintItemData->orderItem === null || $complaintItemData->quantity <= $orderedQuantity) {
                continue;
            }

            $message = t('Quantity of "%itemName%" item must not be greater than the ordered quantity (%orderedQuantity%)', [
                '%itemName%' => $complaintItemData->productName,
                '%orderedQuantity%' => $orderedQuantity,
            ], Translator::VALIDATOR_TRANSLATION_DOMAIN);
            $context
                ->buildViolation($message)
                ->atPath(sprintf('complaintItems[%s].quantity', $key))
                ->addViolation();
        }
    }
}
