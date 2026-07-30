<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Store;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\AddressCoordinates\GoogleAddressCoordinatesFacade;
use Shopsys\FrameworkBundle\Form\Admin\Store\OpeningHours\OpeningHoursRangeCollectionFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreData;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFriendlyUrlProvider;
use Spatie\OpeningHours\Exceptions\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class StoreFormType extends AbstractType
{
    private ?Store $store = null;

    public function __construct(
        private readonly StockFacade $stockFacade,
        private readonly StoreFacade $storeFacade,
        private readonly CountryFacade $countryFacade,
        private readonly StoreOpeningHoursProvider $storeOpeningHoursProvider,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly GoogleAddressCoordinatesFacade $googleAddressCoordinatesFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['store'] instanceof Store) {
            $this->store = $options['store'];
        }

        $builder
            ->add($this->createBasicInformationGroup($builder, $options['store']))
            ->add($this->createAddressGroup($builder))
            ->add($this->createUserInformationGroup($builder))
            ->add($this->createMapGroup($builder))
            ->add($this->createImagesGroup($builder, $options))
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_store_list',
                'entity' => $options['store'],
            ]);
    }

    private function createBasicInformationGroup(FormBuilderInterface $builder, ?Store $store): FormBuilderInterface
    {
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if ($store !== null) {
            $builderBasicInformationGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $store->getId(),
                    'label' => 'ID',
                ])
                ->add('isDefault', DisplayOnlyType::class, [
                    'required' => false,
                    'data' => $store->isDefault() ? t('Yes') : t('No'),
                    'label' => 'Default store',
                ])
                ->add('urls', UrlListType::class, [
                    'route_name' => StoreFriendlyUrlProvider::ROUTE_NAME,
                    'entity_id' => $store->getId(),
                    'label' => 'URL settings',
                    'limit_domains_by_ids' => [$store->getDomainId()],
                ]);
        }

        $builderBasicInformationGroup
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter name'),
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'Name cannot be longer than {{ limit }} characters',
                    ),
                ],
                'label' => 'Name',
            ])
            ->add('domainId', DomainType::class, [
                'required' => true,
                'label' => 'Display on',
                'disabled' => $store !== null,
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 255,
                        maxMessage: 'External ID cannot be longer than {{ limit }} characters',
                    ),
                    new Constraints\Callback(callback: [$this, 'sameStoreExternalIdValidation']),
                ],
                'label' => 'External ID',
            ])
            ->add('stock', ChoiceType::class, [
                'required' => false,
                'label' => 'Warehouse',
                'placeholder' => 'No warehouse associated',
                'choices' => $this->stockFacade->getAllStocks(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => false,
                'expanded' => false,
            ]);

        return $builderBasicInformationGroup;
    }

    private function createUserInformationGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderUserInformationGroup = $builder->create('userInformation', GroupType::class, [
            'label' => 'Information for customers',
        ]);

        $builderUserInformationGroup
            ->add('specialMessage', TextType::class, [
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('openingHours', CollectionType::class, [
                'label' => 'Opening hours',
                'help' => t('Enter in the store local time'),
                'entry_type' => OpeningHoursRangeCollectionFormType::class,
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('description', CKEditorType::class, [
                'required' => false,
            ])
            ->add('directions', CKEditorType::class, [
                'required' => false,
            ]);

        return $builderUserInformationGroup;
    }

    private function createMapGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderMapGroup = $builder->create('map', GroupType::class, [
            'label' => 'Map coordinates in decimal format',
        ]);

        $builderMapGroup
            ->add('latitude', NumberType::class, [
                'required' => false,
                'scale' => 10,
                'attr' => [
                    'class' => 'js-store-coordinate-latitude',
                ],
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'scale' => 10,
                'attr' => [
                    'class' => 'js-store-coordinate-longitude',
                ],
            ]);

        if ($this->googleAddressCoordinatesFacade->isGoogleApiAvailable()) {
            $builderMapGroup->add('loadCoordinates', ButtonType::class, [
                'label' => 'Load coordinates by address',
                'attr' => [
                    'class' => 'btn btn-primary js-load-store-coordinates',
                    'data-load-coordinates-url' => $this->urlGenerator->generate('admin_store_loadcoordinates'),
                ],
            ]);
        }

        return $builderMapGroup;
    }

    private function createAddressGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderAddressGroup = $builder->create('address', GroupType::class, [
            'label' => 'Address',
        ]);

        $builderAddressGroup
            ->add('street', TextType::class, [
                'label' => 'Street',
                'required' => true,
                'attr' => [
                    'class' => 'js-store-address-street',
                ],
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter street'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Street name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'required' => true,
                'attr' => [
                    'class' => 'js-store-address-city',
                ],
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter city'),
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'City name cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Postcode',
                'required' => true,
                'attr' => [
                    'class' => 'js-store-address-postcode',
                ],
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter zip code'),
                    new Constraints\Length(
                        max: 30,
                        maxMessage: 'Zip code cannot be longer than {{ limit }} characters',
                    ),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'required' => true,
                'choices' => $this->countryFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'choice_attr' => static fn (Country $country) => [
                    'data-country-code' => $country->getCode(),
                ],
                'attr' => [
                    'class' => 'js-store-address-country',
                ],
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please choose country'),
                ],
            ]);

        return $builderAddressGroup;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['store'])
            ->setAllowedTypes('store', [Store::class, 'null'])
            ->setDefaults([
                'data_class' => StoreData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => new Constraints\Callback(callback: [$this, 'validateOpeningHours']),
            ]);
    }

    public function sameStoreExternalIdValidation(?string $externalId, ExecutionContextInterface $context): void
    {
        if ($externalId === null) {
            return;
        }

        if ($this->store !== null && $externalId === $this->store->getExternalId()) {
            return;
        }

        $store = $this->storeFacade->findStoreByExternalId($externalId);

        if ($store !== null) {
            $context->addViolation('Store with this external ID already exists');
        }
    }

    private function createImagesGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder->create('imageGroup', GroupType::class, [
            'label' => 'Images',
        ])->add('image', ImageUploadType::class, [
            'required' => false,
            'image_entity_class' => Store::class,
            'file_constraints' => [
                new Constraints\File(
                    maxSize: '2M',
                    maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                        . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                ),
            ],
            'label' => 'Upload image',
            'entity' => $options['store'],
            'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
        ]);
    }

    public function validateOpeningHours(StoreData $storeData, ExecutionContextInterface $context): void
    {
        try {
            $this->storeOpeningHoursProvider->getOpeningHoursSettingFromData($storeData->openingHours);
        } catch (Exception) {
            $context
                ->buildViolation(t('Opening hours setting is not valid', [], 'validators'))
                ->atPath('openingHours')
                ->addViolation();
        }
    }
}
