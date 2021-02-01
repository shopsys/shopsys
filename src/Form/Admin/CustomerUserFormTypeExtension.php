<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [
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
        /** @var CustomerUser|null $customerUser */
        $customerUser = $options['customerUser'];
        $systemData = $builder->get('systemData');
        $systemData->add('erpCustomerNumber', DisplayOnlyType::class, [
            'label' => t('Číslo zákazníka Moeve'),
            'data' => $customerUser !== null ? $customerUser->getErpCustomerNumber() : '',
        ]);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);

        if ($customerUser !== null) {
            $builderSystemDataGroup = $builder->get('systemData');
            $builderSystemDataGroup->add('activated', DisplayOnlyType::class, [
                'label' => t('Aktivní'),
                'data' => $customerUser->isActivated() ? t('Ano') : t('Ne'),
                'position' => ['after' => 'formId'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CustomerUserFormType::class;
    }
}
