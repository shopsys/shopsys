<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserLoginInformationProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerUserFormType extends AbstractType
{
    private ?CustomerUser $customerUser = null;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserLoginInformationProvider $customerUserLoginInformationProvider
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade $salesRepresentativeFacade
     */
    public function __construct(
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
        private readonly CustomerUserFacade $customerUserFacade,
        private readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        private readonly Domain $domain,
        private readonly CustomerFacade $customerFacade,
        private readonly CustomerUserLoginInformationProvider $customerUserLoginInformationProvider,
        private readonly SalesRepresentativeFacade $salesRepresentativeFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->customerUser = $options['customerUser'];
        $domain = $this->domain->getDomainConfigById($options['domain_id']);

        $builderSystemDataGroup = $builder->create('systemData', GroupType::class, [
            'label' => t('System data'),
        ]);

        if ($this->customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $this->customerUser->getId(),
            ]);
            $builderSystemDataGroup->add('domainIcon', DisplayOnlyDomainIconType::class, [
                'label' => t('Domain'),
                'data' => $this->customerUser->getDomainId(),
            ]);
            $builderSystemDataGroup->add('activated', DisplayOnlyType::class, [
                'label' => t('Active'),
                'data' => $this->customerUser->isActivated() ? t('Yes') : t('No'),
                'position' => ['after' => 'formId'],
            ]);
            $pricingGroups = $this->pricingGroupFacade->getByDomainId($this->customerUser->getDomainId());
        } else {
            $builderSystemDataGroup
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                    'attr' => [
                        'class' => 'js-toggle-opt-group-control',
                    ],
                    'disabled' => !$options['allowEditSystemData'],
                ]);
            $pricingGroups = $this->pricingGroupFacade->getAll();
        }

        $builderSystemDataGroup
            ->add('pricingGroup', ChoiceType::class, [
                'required' => true,
                'choices' => $pricingGroups,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_attr' => function (PricingGroup $pricingGroup) {
                    return ['class' => 'js-select-toggle-option-' . $pricingGroup->getDomainId()];
                },
                'label' => t('Pricing group'),
                'attr' => [
                    'class' => 'js-toggle-opt-group',
                    'data-js-toggle-opt-group-control' => '.js-toggle-opt-group-control',
                ],
                'disabled' => !$options['allowEditSystemData'],
            ]);

        $builderSystemDataGroup
            ->add('salesRepresentative', ChoiceType::class, [
                'required' => false,
                'choices' => $this->salesRepresentativeFacade->getAll(),
                'choice_label' => 'fullName',
                'choice_value' => 'id',
                'label' => t('Sales representative'),
                'disabled' => !$options['allowEditSystemData'],
            ]);

        $builderPersonalDataGroup = $builder->create('personalData', GroupType::class, [
            'label' => t('Personal data'),
        ]);

        if (
            ($domain->isB2b() && $this->customerUser === null) ||
            ($this->customerUser !== null && $this->customerFacade->isB2bFeaturesEnabledByCustomer($this->customerUser->getCustomer()))
        ) {
            $roleGroups = $this->customerUserRoleGroupFacade->getAll();
            $builderPersonalDataGroup
                ->add('roleGroup', ChoiceType::class, [
                    'required' => true,
                    'choices' => $roleGroups,
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'label' => t('Role'),
                ]);
        }

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('First name'),
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter last name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Last name'),
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter email']),
                    new Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Email cannot be longer than {{ limit }} characters',
                    ]),
                    new Email(['message' => 'Please enter valid email']),
                    new Constraints\Callback([$this, 'validateUniqueEmail']),
                ],
                'label' => t('Email'),
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length([
                        'max' => 30,
                        'maxMessage' => 'Telephone number cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'label' => t('Telephone'),
            ]);

        if ($this->customerUser === null) {
            $builderPersonalDataGroup->add('sendRegistrationMail', CheckboxType::class, [
                'required' => false,
                'label' => t('Send confirmation email about registration to customer'),
                'position' => ['after' => 'email'],
            ]);
        }


        if ($this->customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('createdAt', DisplayOnlyType::class, [
                'label' => t('Date of registration and privacy policy agreement'),
                'data' => $this->dateTimeFormatterExtension->formatDateTime($this->customerUser->getCreatedAt()),
            ]);

            $builderSystemDataGroup->add('lastLogin', DisplayOnlyType::class, [
                'label' => t('Last login'),
                'data' => $this->customerUserLoginInformationProvider->getLastLogin($this->customerUser) !== null ? $this->dateTimeFormatterExtension->formatDateTime(
                    $this->customerUserLoginInformationProvider->getLastLogin($this->customerUser),
                ) : t(
                    'never',
                ),
            ]);

            if ($this->customerUserLoginInformationProvider->getAdditionalLoginInfo($this->customerUser) !== null) {
                $builderSystemDataGroup->add('additionalLoginInfo', DisplayOnlyType::class, [
                    'label' => t('Additional login info'),
                    'data' => $this->customerUserLoginInformationProvider->getAdditionalLoginInfo($this->customerUser),
                ]);
            }
        }

        $builder
            ->add($builderSystemDataGroup)
            ->add($builderPersonalDataGroup);

        if ($options['renderSaveButton']) {
            $builder->add('save', SubmitType::class);
        }
    }

    /**
     * @param string|null $email
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniqueEmail(?string $email, ExecutionContextInterface $context): void
    {
        if ($email === null) {
            return;
        }

        /** @var \Symfony\Component\Form\Form $form */
        $form = $context->getRoot();

        if ($form->getData() instanceof CustomerUserData) {
            $customerUserData = $form->getData();
        } else {
            /** @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData */
            $customerUserData = $form->getData()->customerUserData;
        }

        $domainId = $customerUserData->domainId;
        $existingCustomerWithEmail = $this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId);

        if ($existingCustomerWithEmail !== null && $existingCustomerWithEmail !== $this->customerUser) {
            $context->addViolation('The email is already registered on given domain.');
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['customerUser', 'domain_id'])
            ->setDefined(['renderSaveButton', 'allowEditSystemData'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('renderSaveButton', 'bool')
            ->setAllowedTypes('allowEditSystemData', 'bool')
            ->setDefaults([
                'data_class' => CustomerUserData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new FieldsAreNotIdentical([
                        'field1' => 'email',
                        'field2' => 'password',
                        'errorPath' => 'password',
                        'message' => 'Password cannot be same as email',
                    ]),
                    new NotIdenticalToEmailLocalPart([
                        'password' => 'password',
                        'email' => 'email',
                        'errorPath' => 'password',
                        'message' => 'Password cannot be same as part of email before at sign',
                    ]),
                ],
                'renderSaveButton' => false,
                'allowEditSystemData' => true,
            ]);
    }
}
