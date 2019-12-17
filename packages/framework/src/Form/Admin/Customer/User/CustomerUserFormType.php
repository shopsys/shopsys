<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Customer\User;

use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CustomerUserFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    private $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension
     */
    private $dateTimeFormatterExtension;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(
        PricingGroupFacade $pricingGroupFacade,
        DateTimeFormatterExtension $dateTimeFormatterExtension
    ) {
        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->dateTimeFormatterExtension = $dateTimeFormatterExtension;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $customerUser \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser */
        $customerUser = $options['customerUser'];

        $builderSystemDataGroup = $builder->create('systemData', GroupType::class, [
            'label' => t('System data'),
        ]);

        if ($customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'data' => $customerUser->getId(),
            ]);
            $builderSystemDataGroup->add('domainIcon', DisplayOnlyDomainIconType::class, [
                'data' => $customerUser->getDomainId(),
            ]);
            $pricingGroups = $this->pricingGroupFacade->getByDomainId($options['domain_id']);
            $groupPricingGroupsBy = null;
        } else {
            $builderSystemDataGroup
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                    'attr' => [
                        'class' => 'js-toggle-opt-group-control',
                    ],
                ]);
            $pricingGroups = $this->pricingGroupFacade->getAll();
            $groupPricingGroupsBy = 'domainId';
        }

        $builderSystemDataGroup
            ->add('pricingGroup', ChoiceType::class, [
                'required' => true,
                'choices' => $pricingGroups,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'group_by' => $groupPricingGroupsBy,
                'label' => t('Pricing group'),
                'attr' => [
                    'class' => 'js-toggle-opt-group',
                    'data-js-toggle-opt-group-control' => '.js-toggle-opt-group-control',
                ],
            ]);

        $builderPersonalDataGroup = $builder->create('personalData', GroupType::class, [
            'label' => t('Personal data'),
        ]);

        $builderPersonalDataGroup
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter first name']),
                    new Constraints\Length([
                        'max' => 100,
                        'maxMessage' => 'First name cannot be longer then {{ limit }} characters',
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
                    new Constraints\NotBlank(['message' => 'Please enter e-mail']),
                    new Constraints\Length([
                        'max' => 255,
                        'maxMessage' => 'Email cannot be longer then {{ limit }} characters',
                    ]),
                    new Email(['message' => 'Please enter valid e-mail']),
                    new UniqueEmail([
                        'ignoredEmail' => $customerUser !== null ? $customerUser->getEmail() : null,
                        'domainId' => $options['domain_id'],
                    ]),
                ],
                'label' => t('E-mail'),
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

        $builderRegisteredCustomerGroup = $builder->create('registeredCustomer', GroupType::class, [
            'label' => t('Registered cust.'),
        ]);

        $builderRegisteredCustomerGroup
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['customerUser'] === null,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'constraints' => $this->getFirstPasswordConstraints($options['customerUser'] === null),
                    'label' => t('Password'),
                ],
                'second_options' => [
                    'label' => t('Password again'),
                ],
                'invalid_message' => 'Passwords do not match',
            ]);

        if ($customerUser instanceof CustomerUser) {
            $builderSystemDataGroup->add('createdAt', DisplayOnlyType::class, [
                'label' => t('Date of registration and privacy policy agreement'),
                'data' => $this->dateTimeFormatterExtension->formatDateTime($customerUser->getCreatedAt()),
            ]);

            $builderRegisteredCustomerGroup->add('lastLogin', DisplayOnlyType::class, [
                'label' => t('Last login'),
                'data' => $customerUser->getLastLogin() !== null ? $this->dateTimeFormatterExtension->formatDateTime($customerUser->getLastLogin()) : t('never'),
            ]);
        }

        $builder
            ->add($builderSystemDataGroup)
            ->add($builderPersonalDataGroup)
            ->add($builderRegisteredCustomerGroup);
    }

    /**
     * @param bool $isCreatingNewUser
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getFirstPasswordConstraints($isCreatingNewUser)
    {
        $constraints = [
            new Constraints\Length(['min' => 6, 'minMessage' => 'Password cannot be longer then {{ limit }} characters']),
        ];

        if ($isCreatingNewUser) {
            $constraints[] = new Constraints\NotBlank([
                'message' => 'Please enter password',
            ]);
        }

        return $constraints;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['customerUser', 'domain_id'])
            ->setAllowedTypes('customerUser', [CustomerUser::class, 'null'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => CustomerUserData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new FieldsAreNotIdentical([
                        'field1' => 'email',
                        'field2' => 'password',
                        'errorPath' => 'password',
                        'message' => 'Password cannot be same as e-mail',
                    ]),
                    new NotIdenticalToEmailLocalPart([
                        'password' => 'password',
                        'email' => 'email',
                        'errorPath' => 'password',
                        'message' => 'Password cannot be same as part of e-mail before at sign',
                    ]),
                ],
            ]);
    }
}
