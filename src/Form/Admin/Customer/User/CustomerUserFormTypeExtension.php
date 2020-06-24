<?php

declare(strict_types=1);

namespace App\Form\Admin\Customer\User;

use Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

//TODO - this extension is fix for https://github.com/shopsys/shopsys/pull/1902 , after update sswf remove this extension.
class CustomerUserFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    private $customerUserFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUser
     */
    private $customerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(CustomerUserFacade $customerUserFacade)
    {
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->customerUser = $options['customerUser'];

        $personalDataBuilder = $builder->get('personalData');

        $personalDataBuilder->remove('email');
        $personalDataBuilder->add('email', EmailType::class, [
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
        ]);
    }

    /**
     * @param string $email
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniqueEmail(string $email, ExecutionContextInterface $context): void
    {
        /** @var \Symfony\Component\Form\Form $form */
        $form = $context->getRoot();
        /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
        $customerUserData = $form->getData()->customerUserData;

        $domainId = $customerUserData->domainId;
        if ($this->customerUserFacade->findCustomerUserByEmailAndDomain($email, $domainId) !== $this->customerUser) {
            $context->addViolation('The email is already registered on given domain.');
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
