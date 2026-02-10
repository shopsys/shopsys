<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserLoginInformationProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CustomerUserFormType extends AbstractType
{
    private ?CustomerUser $customerUser = null;

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

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->customerUser = $options['customerUser'];
        $domain = $this->domain->getDomainConfigById($options['domain_id']);

        $builderSystemDataGroup = $builder->create('systemData', GroupType::class, [
            'label' => 'System data',
        ]);

        if ($this->customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('formId', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $this->customerUser->getId(),
            ]);
            $builderSystemDataGroup->add('activated', DisplayOnlyType::class, [
                'label' => 'Active',
                'data' => $this->customerUser->isActivated() ? t('Yes') : t('No'),
            ]);
        }

        $domainId = $this->customerUser?->getDomainId() ?? $options['domain_id'];
        $builderSystemDataGroup->add('domainIcon', DisplayOnlyDomainIconType::class, [
            'label' => 'Domain',
            'data' => $domainId,
        ]);
        $pricingGroups = $this->pricingGroupFacade->getByDomainId($domainId);

        $builderSystemDataGroup
            ->add('pricingGroup', ChoiceType::class, [
                'required' => true,
                'choices' => $pricingGroups,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Pricing group',
                'disabled' => !$options['allowEditSystemData'],
            ]);

        $builderSystemDataGroup
            ->add('salesRepresentative', ChoiceType::class, [
                'required' => false,
                'choices' => $this->salesRepresentativeFacade->getAll(),
                'choice_label' => 'fullName',
                'choice_value' => 'id',
                'label' => 'Sales representative',
                'disabled' => !$options['allowEditSystemData'],
            ]);

        $builderPersonalDataGroup = $builder->create('personalData', GroupType::class, [
            'label' => 'Personal data',
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
                    'label' => 'Role',
                ]);
        }

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter first name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'First name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'First name',
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter last name'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Last name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Last name',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter email'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Email cannot be longer than {{ limit }} characters',
                    ),
                    new Email(message: 'Please enter valid email'),
                    new Constraints\Callback(callback: [$this, 'validateUniqueEmail']),
                ],
                'label' => 'Email',
            ]);

        if ($this->customerUser === null) {
            $builderPersonalDataGroup->add('sendRegistrationMail', CheckboxType::class, [
                'required' => false,
                'label' => 'Send confirmation email about registration to customer',
            ]);
        }

        $builderPersonalDataGroup
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Telephone number cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Telephone',
            ]);

        if ($this->customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('createdAt', DisplayOnlyType::class, [
                'label' => 'Date of registration and privacy policy agreement',
                'data' => $this->dateTimeFormatterExtension->formatDateTime($this->customerUser->getCreatedAt()),
            ]);

            $builderSystemDataGroup->add('lastLogin', DisplayOnlyType::class, [
                'label' => 'Last login',
                'data' => $this->customerUserLoginInformationProvider->getLastLogin($this->customerUser) !== null ? $this->dateTimeFormatterExtension->formatDateTime(
                    $this->customerUserLoginInformationProvider->getLastLogin($this->customerUser),
                ) : t(
                    'never',
                ),
            ]);

            if ($this->customerUserLoginInformationProvider->getAdditionalLoginInfo($this->customerUser) !== null) {
                $builderSystemDataGroup->add('additionalLoginInfo', DisplayOnlyType::class, [
                    'label' => 'Additional login info',
                    'data' => $this->customerUserLoginInformationProvider->getAdditionalLoginInfo($this->customerUser),
                ]);
            }
        }

        $builder
            ->add($builderSystemDataGroup)
            ->add($builderPersonalDataGroup);

        if ($options['renderSaveButton']) {
            $builder->add('actionBar', ActionBarType::class, [
                'back_url' => $options['back_url'],
                'back_label' => $options['back_url_text'],
                'entity' => $options['customerUser'],
            ]);
        }
    }

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

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['customerUser', 'domain_id', 'back_url', 'back_url_text'])
            ->setDefined(['renderSaveButton', 'allowEditSystemData'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('renderSaveButton', 'bool')
            ->setAllowedTypes('allowEditSystemData', 'bool')
            ->setAllowedTypes('back_url', ['string', 'null'])
            ->setAllowedTypes('back_url_text', ['string', 'null'])
            ->setDefaults([
                'data_class' => CustomerUserData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'renders_in_own_card' => true,
                'constraints' => [
                    new FieldsAreNotIdentical(
                        field1: 'email',
                        field2: 'password',
                        errorPath: 'password',
                        message: 'Password cannot be same as email',
                    ),
                    new NotIdenticalToEmailLocalPart(
                        password: 'password',
                        email: 'email',
                        errorPath: 'password',
                        message: 'Password cannot be same as part of email before at sign',
                    ),
                ],
                'renderSaveButton' => false,
                'allowEditSystemData' => true,
                'back_url' => null,
                'back_url_text' => null,
            ]);
    }
}
