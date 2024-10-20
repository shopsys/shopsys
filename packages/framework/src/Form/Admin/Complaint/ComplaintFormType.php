<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\Complaint\Status\ComplaintItemsType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyCustomerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyOrderType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ComplaintFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade $complaintStatusFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        protected readonly CountryFacade $countryFacade,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add($this->createBasicInformationGroup($builder, $options['complaint']));
        $builder->add($this->createDeliveryAddressGroup($builder));
        $builder->add($this->createItemsGroup($builder));

        $builder->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
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
                'constraints' => [new Constraints\Callback([$this, 'validateQuantityIsLessOrEqualThanOrdered'])],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Complaint $complaint
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createBasicInformationGroup(
        FormBuilderInterface $builder,
        Complaint $complaint,
    ): FormBuilderInterface {
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => t('Basic information'),
        ]);

        $builderBasicInformationGroup
            ->add('id', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $complaint->getId(),
            ]);

        if ($this->domain->isMultidomain()) {
            $builderBasicInformationGroup
                ->add('domainIcon', DisplayOnlyDomainIconType::class, [
                    'label' => t('Domain'),
                    'data' => $complaint->getDomainId(),
                ]);
        }

        $builderBasicInformationGroup
            ->add('number', DisplayOnlyType::class, [
                'label' => t('Complaint number'),
                'data' => $complaint->getNumber(),
            ])
            ->add('dateOfCreation', DisplayOnlyType::class, [
                'label' => t('Date of creation'),
                'data' => $this->dateTimeFormatterExtension->formatDateTime($complaint->getCreatedAt()),
            ])
            ->add('status', ChoiceType::class, [
                'label' => t('Status'),
                'required' => true,
                'choices' => $this->complaintStatusFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('order', DisplayOnlyOrderType::class, [
                'label' => t('Order'),
                'order' => $complaint->getOrder(),
            ])
            ->add('user', DisplayOnlyCustomerType::class, [
                'label' => t('Customer'),
                'user' => $complaint->getCustomerUser(),
            ]);

        return $builderBasicInformationGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createDeliveryAddressGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDeliveryAddressGroup = $builder->create('deliveryAddressGroup', GroupType::class, [
            'label' => t('Delivery address'),
        ]);

        $builderDeliveryAddressGroup
            ->add('deliveryFirstName', TextType::class, [
                'label' => t('First name'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter first name of contact person',
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name of contact person cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryLastName', TextType::class, [
                'label' => t('Last name'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter last name of contact person',
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Last name of contact person cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryCompanyName', TextType::class, [
                'label' => t('Company'),
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryTelephone', TextType::class, [
                'label' => t('Telephone'),
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryStreet', TextType::class, [
                'label' => t('Street'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter street',
                    ]),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Street name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => t('City'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter city',
                    ]),
                    new Constraints\Length(['max' => 100,
                        'maxMessage' => 'City name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryPostcode', TextType::class, [
                'label' => t('Postcode'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank([
                        'message' => 'Please enter zip code',
                    ]),
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Zip code cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('deliveryCountry', ChoiceType::class, [
                'label' => t('Country'),
                'required' => true,
                'choices' => $this->countryFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please choose country']),
                ],
            ]);

        return $builderDeliveryAddressGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createItemsGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderItemsGroup = $builder->create('itemsGroup', GroupType::class, [
            'label' => t('Complaint items'),
        ]);

        $builderItemsGroup
            ->add('complaintItems', ComplaintItemsType::class, [
                'label' => false,
                'entry_type' => ComplaintItemFormType::class,
                'error_bubbling' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ]);

        return $builderItemsGroup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateQuantityIsLessOrEqualThanOrdered(
        ComplaintData $complaintData,
        ExecutionContextInterface $context,
    ): void {
        foreach ($complaintData->complaintItems as $complaintItemData) {
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
                ->atPath('complaintItems')
                ->addViolation();
        }
    }
}
