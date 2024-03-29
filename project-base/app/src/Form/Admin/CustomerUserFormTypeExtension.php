<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    private const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     */
    public function __construct(private FormBuilderHelper $formBuilderHelper)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $options['customerUser'];

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);

        if ($customerUser === null) {
            return;
        }

        $builderSystemDataGroup = $builder->get('systemData');
        $builderSystemDataGroup->add('activated', DisplayOnlyType::class, [
            'label' => t('Active'),
            'data' => $customerUser->isActivated() ? t('Yes') : t('No'),
            'position' => ['after' => 'formId'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CustomerUserFormType::class;
    }
}
