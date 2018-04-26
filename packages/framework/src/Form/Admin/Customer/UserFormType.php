<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Customer;

use Shopsys\FrameworkBundle\Component\Constraints\Email;
use Shopsys\FrameworkBundle\Component\Constraints\FieldsAreNotIdentical;
use Shopsys\FrameworkBundle\Component\Constraints\NotIdenticalToEmailLocalPart;
use Shopsys\FrameworkBundle\Component\Constraints\UniqueEmail;
use Shopsys\FrameworkBundle\Form\DisplayOnlyDomainIconType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyTextType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class UserFormType extends AbstractType
{
    const DATE_TIME_FORMAT = 'Y-m-d, h:i:s A';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    private $pricingGroupFacade;

    public function __construct(PricingGroupFacade $pricingGroupFacade)
    {
        $this->pricingGroupFacade = $pricingGroupFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        /* @var $user \Shopsys\FrameworkBundle\Model\Customer\User */

        $builderSystemDataBuilder = $builder->create('system_data', GroupType::class, [
            'label' => t('System data'),
        ]);

        if ($options['scenario'] === CustomerFormType::SCENARIO_CREATE) {
            $builderSystemDataBuilder
                ->add('domainId', DomainType::class, [
                    'required' => true,
                    'data' => $options['domain_id'],
                    'label' => t('Domain'),
                ]);
            $pricingGroups = $this->pricingGroupFacade->getAll();
            $groupPricingGroupsBy = 'domainId';
        } else {
            $pricingGroups = $this->pricingGroupFacade->getByDomainId($options['domain_id']);
            $groupPricingGroupsBy = null;
        }

        $builderSystemDataBuilder
            ->add('pricingGroup', ChoiceType::class, [
                'required' => true,
                'choices' => $pricingGroups,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'group_by' => $groupPricingGroupsBy,
                'label' => t('Pricing groups'),
            ]);

        $builderPersonalDataGroup = $builder->create('personal_data', GroupType::class, [
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
                    new UniqueEmail(['ignoredEmail' => $user !== null ? $user->getEmail() : null]),
                ],
                'label' => t('Personal data'),
            ]);

        $builderRegisteredCustGroup = $builder->create('registered_cust', GroupType::class, [
            'label' => t('Registered cust.'),
        ]);

        $builderRegisteredCustGroup
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['scenario'] === CustomerFormType::SCENARIO_CREATE,
                'options' => [
                    'attr' => ['autocomplete' => 'off'],
                ],
                'first_options' => [
                    'label' => t('Password'),
                    'constraints' => $this->getFirstPasswordConstraints($options['scenario']),
                ],
                'second_options' => [
                    'label' => t('Password again'),
                ],
                'invalid_message' => 'Passwords do not match',
            ]);

        if ($user instanceof User) {
            $builderSystemDataBuilder->add('formId', DisplayOnlyType::class, [
                'label' => t('ID'),
                'widget_form_type_data' => $user->getId(),
            ]);

            $builderSystemDataBuilder->add('domainIcon', DisplayOnlyType::class, [
                'widget_form_type' => DisplayOnlyDomainIconType::class,
                'widget_form_type_data' => $user->getDomainId(),
            ]);

            $builderSystemDataBuilder->add('createdAt', DisplayOnlyType::class, [
                'label' => t('Date of registration and privacy policy agreement'),
                'widget_form_type_data' => $user->getCreatedAt()->format(self::DATE_TIME_FORMAT),
            ]);

            $builderRegisteredCustGroup->add('lastLogin', DisplayOnlyType::class, [
                'label' => t('Last login'),
                'widget_form_type' => TextType::class,
                'widget_form_type_data' => $user->getLastLogin() !== null ? $user->getLastLogin()->format(self::DATE_TIME_FORMAT) : 'never',
            ]);
        }

        $builder
            ->add($builderSystemDataBuilder)
            ->add($builderPersonalDataGroup)
            ->add($builderRegisteredCustGroup);
    }

    /**
     * @param string $scenario
     * @return \Symfony\Component\Validator\Constraint[]
     */
    private function getFirstPasswordConstraints($scenario)
    {
        $constraints = [
            new Constraints\Length(['min' => 6, 'minMessage' => 'Password cannot be longer then {{ limit }} characters']),
        ];

        if ($scenario === CustomerFormType::SCENARIO_CREATE) {
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
            ->setRequired(['scenario', 'domain_id'])
            ->setAllowedValues('scenario', [CustomerFormType::SCENARIO_CREATE, CustomerFormType::SCENARIO_EDIT])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'data_class' => UserData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'user' => null,
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
            ])
            ->setAllowedTypes('user', [User::class, 'null']);
    }
}
