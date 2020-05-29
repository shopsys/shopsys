<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [
        'gender',
        'email',
        'firstName',
        'lastName',
        'telephone',
    ];

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(FormBuilderHelper $formBuilderHelper)
    {
        $this->formBuilderHelper = $formBuilderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $customerUser \App\Model\Customer\User\CustomerUser */
        $customerUser = $options['customerUser'];
        $isCompanyCustomer = false;
        if ($customerUser !== null) {
            $isCompanyCustomer = $customerUser->getCustomer()->getBillingAddress()->isCompanyCustomer();
        }

        if (!$isCompanyCustomer) {
            $personalDataBuilder = $builder->get('personalData');
            $personalDataBuilder->add('gender', ChoiceType::class, [
                'label' => t('Oslovení'),
                'position' => 'first',
                'choices' => array_flip(CustomerUser::getAllGenders()),
                'placeholder' => t('-- Vyber oslovení --'),
                'constraints' => [
                    new NotBlank(['message' => 'Please choose your gender']),
                ],
            ]);
        }

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CustomerUserFormType::class;
    }
}
