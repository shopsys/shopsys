<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Product\Type\ProductTypeFacade;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportData;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TransportFormTypeExtension extends AbstractTypeExtension
{
    public const VALIDATION_GROUP_TYPE_PACKAGE = 'type_package';

    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(ProductTypeFacade $productTypeFacade)
    {
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->get('basicInformation')
            ->add('productTypes', ChoiceType::class, [
                'required' => false,
                'choices' => $this->productTypeFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'label' => t('Určeno pro typy produktů'),
            ])
            ->add('personalPickup', YesNoType::class, [
                'required' => false,
                'label' => t('Osobní odběr SCONTO'),
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    t('Běžná') => Transport::TYPE_COMMON,
                    t('Balíková') => Transport::TYPE_PACKAGE,
                    t('Paletová') => Transport::TYPE_PALLET,
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => t('Typ'),
            ]);

        $builder->add($this->createTransportPackages($builder));
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createTransportPackages(FormBuilderInterface $builder): FormBuilderInterface
    {
        $packagesGroupBuilder = $builder->create('packagesGroup', GroupType::class, [
            'label' => t('Balíková přeprava'),
            'position' => ['after' => 'basicInformation'],
        ]);

        $packagesGroupBuilder->add('transportPackages', CollectionType::class, [
            'required' => true,
            'label' => t('Druhy balíků'),
            'entry_type' => TransportPackageFormType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        return $packagesGroupBuilder;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'constraints' => [
                    new Callback([
                        'groups' => [self::VALIDATION_GROUP_TYPE_PACKAGE],
                        'callback' => [$this, 'validateLeastOnePackageOnEachAllowedDomain'],
                    ]),
                ],
                'validation_groups' => function (FormInterface $form) {
                    $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];
                    /** @var \App\Model\Transport\TransportData $transportData */
                    $transportData = $form->getData();

                    if ($transportData->type === Transport::TYPE_PACKAGE) {
                        $validationGroups[] = self::VALIDATION_GROUP_TYPE_PACKAGE;
                    }

                    return $validationGroups;
                },
            ]);
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateLeastOnePackageOnEachAllowedDomain(TransportData $transportData, ExecutionContextInterface $context): void
    {
        if ($transportData->type !== Transport::TYPE_PACKAGE) {
            return;
        }

        foreach ($transportData->enabled as $domainId => $enabled) {
            if ($enabled === true) {
                $existsPackageForDomain = false;
                foreach ($transportData->transportPackages as $transportPackageData) {
                    if ($transportPackageData->domainId === $domainId) {
                        $existsPackageForDomain = true;
                        break;
                    }
                }

                if ($existsPackageForDomain === false) {
                    $errorMessage = t(
                        'Musíte nastavit alespoň jeden druh balík pro %domainNumber%. doménu, jelikož je zde tato doprava povolena.',
                        ['%domainNumber%' => $domainId]
                    );
                    $context->buildViolation($errorMessage)
                        ->atPath('type') // I can not set to `transportPackages` and I do not know how to solve it :(
                        ->addViolation();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield TransportFormType::class;
    }
}
