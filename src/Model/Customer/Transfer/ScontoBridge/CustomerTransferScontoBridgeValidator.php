<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerTransferScontoBridgeValidator
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array $scontoBridgeCustomerData
     */
    public function validate(array $scontoBridgeCustomerData): void
    {
        $violations = $this->validator->validate($scontoBridgeCustomerData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'erpCustomerNumber' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'int']),
                ],
                'email' => [
                    new Assert\Email(),
                    new Assert\Length(['max' => 255]),
                ],
                'newsletter' => [
                    new Assert\Type(['type' => 'boolean']),
                ],
                'distributionChannelCode' => [
                    new Assert\Choice([
                        CustomerTransferScontoBridgeMapper::DISTRIBUTION_CHANEL_CODE_CZ,
                        CustomerTransferScontoBridgeMapper::DISTRIBUTION_CHANEL_CODE_SK,
                    ]),
                ],
                'customerType' => [
                    new Assert\Choice([
                        CustomerTransferScontoBridgeMapper::CUSTOMER_TYPE_COMPANY,
                        CustomerTransferScontoBridgeMapper::CUSTOMER_TYPE_INDIVIDUAL,
                    ]),
                ],
                'phonePrefix' => [
                    new Assert\Type(['type' => 'int']),
                ],
                'phoneNumber' => [
                    new Assert\Type(['type' => 'int']),
                ],
                'primaryAddress' => [
                    new Assert\Collection([
                        'allowExtraFields' => true,
                        'fields' => [
                            'street' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                            'city' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                            'country' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                            'zipCode' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 30]),
                            ],
                        ],
                    ]),
                ],
                'individual' => [
                    new Assert\Collection([
                        'allowExtraFields' => true,
                        'fields' => [
                            'individualTitle' => [
                                new Assert\Choice([
                                    CustomerTransferScontoBridgeMapper::INDIVIDUAL_TITLE_MALE,
                                    CustomerTransferScontoBridgeMapper::INDIVIDUAL_TITLE_FEMALE,
                                ]),
                            ],
                            'firstName' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                            'lastName' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                        ],
                    ]),
                ],
                'company' => [
                    new Assert\Collection([
                        'allowExtraFields' => true,
                        'fields' => [
                            'name' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 100]),
                            ],
                            'companyNumber' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 50]),
                            ],
                            'vatNumber' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 50]),
                            ],
                            'taxNumber' => [
                                new Assert\Type(['type' => 'string']),
                                new Assert\Length(['max' => 50]),
                            ],
                        ],
                    ]),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }
}
